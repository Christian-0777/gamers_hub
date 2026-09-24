<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../api/comments_func.php';

use Ratchet\App;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

final class GamerSocket implements MessageComponentInterface
{
    private SplObjectStorage $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $connection): void
    {
        $userId = $this->authenticate($connection);
        if ($userId === null) {
            $connection->send($this->encode(['type' => 'error', 'message' => 'Authentication required.']));
            $connection->close();
            return;
        }

        $this->clients->attach($connection, ['user_id' => $userId]);
        $connection->send($this->encode(['type' => 'ready']));
    }

    public function onMessage(ConnectionInterface $from, $message): void
    {
        $client = $this->clients[$from] ?? null;
        if (!$client) {
            $from->close();
            return;
        }

        try {
            $payload = json_decode((string) $message, true, 512, JSON_THROW_ON_ERROR);
            $action = (string) ($payload['action'] ?? '');

            if ($action === 'ping') {
                $from->send($this->encode(['type' => 'pong']));
                return;
            }

            if ($action === 'send') {
                $this->sendMessage($from, $client, $payload);
                return;
            }

            $result = match ($action) {
                'react_post' => $this->reactPost((int) $client['user_id'], $payload),
                'share_post' => $this->sharePost((int) $client['user_id'], $payload),
                'create_comment', 'reply_comment' => $this->createComment((int) $client['user_id'], $payload, $action === 'reply_comment'),
                'react_comment' => $this->reactComment((int) $client['user_id'], $payload),
                default => throw new InvalidArgumentException('Unsupported WebSocket action.'),
            };

            $this->sendActionResult($from, $payload, $result);
            $this->broadcast($result['event']);
        } catch (Throwable $exception) {
            $from->send($this->encode([
                'type' => 'action_result',
                'request_id' => $payload['request_id'] ?? null,
                'success' => false,
                'message' => $exception->getMessage(),
            ]));
        }
    }

    private function sendMessage(ConnectionInterface $from, array $client, array $payload): void
    {
        $conversationId = (int) ($payload['conversation_id'] ?? 0);
        $body = trim((string) ($payload['body'] ?? ''));
        if ($conversationId < 1 || $body === '' || strlen($body) > 4000) {
            throw new InvalidArgumentException('Message must be between 1 and 4000 characters.');
        }

        $database = db();
        if (!$this->conversationAccess($database, $conversationId, (int) $client['user_id'])) {
            throw new RuntimeException('Conversation not found.');
        }

        $statement = $database->prepare(
            'INSERT INTO messages (conversation_id, sender_id, body)
             VALUES (:conversation_id, :sender_id, :body)'
        );
        $statement->execute([
            'conversation_id' => $conversationId,
            'sender_id' => $client['user_id'],
            'body' => $body,
        ]);
        $messageId = (int) $database->lastInsertId();
        $database->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = :id')
            ->execute(['id' => $conversationId]);
        $database->prepare(
            'UPDATE conversation_participants SET last_read_message_id = :message_id
             WHERE conversation_id = :conversation_id AND user_id = :user_id'
        )->execute([
            'message_id' => $messageId,
            'conversation_id' => $conversationId,
            'user_id' => $client['user_id'],
        ]);

        $senderStatement = $database->prepare(
            'SELECT display_name FROM user_profiles WHERE user_id = :user_id LIMIT 1'
        );
        $senderStatement->execute(['user_id' => $client['user_id']]);
        $senderName = (string) ($senderStatement->fetchColumn() ?: 'Gamer');
        $event = [
            'type' => 'message',
            'conversation_id' => $conversationId,
            'message' => [
                'id' => $messageId,
                'body' => $body,
                'sender_id' => (int) $client['user_id'],
                'sender_name' => $senderName,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ],
        ];

        foreach ($this->clients as $connection) {
            $recipient = $this->clients[$connection];
            if ($this->conversationAccess($database, $conversationId, (int) $recipient['user_id'])) {
                $connection->send($this->encode($event));
            }
        }
    }

    private function reactPost(int $userId, array $payload): array
    {
        $postId = (int) ($payload['post_id'] ?? 0);
        $database = db();
        $this->assertPostAccess($database, $postId, $userId);
        $existing = $database->prepare('SELECT 1 FROM post_reactions WHERE post_id = :post_id AND user_id = :user_id LIMIT 1');
        $existing->execute(['post_id' => $postId, 'user_id' => $userId]);
        if ($existing->fetch()) {
            $database->prepare('DELETE FROM post_reactions WHERE post_id = :post_id AND user_id = :user_id')->execute(['post_id' => $postId, 'user_id' => $userId]);
            $liked = false;
        } else {
            $database->prepare('INSERT INTO post_reactions (post_id, user_id, reaction_type) VALUES (:post_id, :user_id, \'like\')')->execute(['post_id' => $postId, 'user_id' => $userId]);
            $liked = true;
            $owner = $database->prepare('SELECT user_id FROM posts WHERE id = :post_id LIMIT 1');
            $owner->execute(['post_id' => $postId]);
            notifyUser($database, (int) $owner->fetchColumn(), $userId, 'reaction', $postId, 'reacted to your post.');
        }
        $count = $database->prepare('SELECT COUNT(*) FROM post_reactions WHERE post_id = :post_id AND reaction_type = \'like\'');
        $count->execute(['post_id' => $postId]);
        $event = ['type' => 'post_reaction', 'post_id' => $postId, 'actor_id' => $userId, 'liked' => $liked, 'like_count' => (int) $count->fetchColumn()];
        return ['event' => $event, 'result' => $event];
    }

    private function sharePost(int $userId, array $payload): array
    {
        $postId = (int) ($payload['post_id'] ?? 0);
        $database = db();
        $this->assertPostAccess($database, $postId, $userId);
        $database->prepare('INSERT INTO post_shares (post_id, user_id) VALUES (:post_id, :user_id)')->execute(['post_id' => $postId, 'user_id' => $userId]);
        $owner = $database->prepare('SELECT user_id FROM posts WHERE id = :post_id LIMIT 1');
        $owner->execute(['post_id' => $postId]);
        notifyUser($database, (int) $owner->fetchColumn(), $userId, 'share', $postId, 'shared your post.');
        $count = $database->prepare('SELECT COUNT(*) FROM post_shares WHERE post_id = :post_id');
        $count->execute(['post_id' => $postId]);
        $event = ['type' => 'post_share', 'post_id' => $postId, 'actor_id' => $userId, 'share_count' => (int) $count->fetchColumn()];
        return ['event' => $event, 'result' => $event];
    }

    private function createComment(int $userId, array $payload, bool $isReply): array
    {
        $postId = (int) ($payload['post_id'] ?? 0);
        $content = trim((string) ($payload['content'] ?? ''));
        $parentId = (int) ($payload['parent_id'] ?? 0);
        if ($content === '' || mb_strlen($content) > 2000) {
            throw new InvalidArgumentException('Comments must contain between 1 and 2,000 characters.');
        }
        if (!$isReply) $parentId = 0;
        if ($isReply && $parentId < 1) throw new InvalidArgumentException('Choose a comment to reply to.');
        $database = db();
        $this->assertPostAccess($database, $postId, $userId);
        if ($parentId > 0) {
            $parent = $database->prepare('SELECT id FROM comments WHERE id = :id AND post_id = :post_id AND status = \'published\' LIMIT 1');
            $parent->execute(['id' => $parentId, 'post_id' => $postId]);
            if (!$parent->fetch()) throw new RuntimeException('The comment you are replying to is unavailable.');
        }
        $insert = $database->prepare('INSERT INTO comments (post_id, user_id, parent_id, content, status) VALUES (:post_id, :user_id, :parent_id, :content, \'published\')');
        $insert->execute(['post_id' => $postId, 'user_id' => $userId, 'parent_id' => $parentId > 0 ? $parentId : null, 'content' => $content]);
        $comment = getPostComment($database, (int) $database->lastInsertId(), $userId);
        $count = $database->prepare('SELECT COUNT(*) FROM comments WHERE post_id = :post_id AND status = \'published\'');
        $count->execute(['post_id' => $postId]);
        $owner = $database->prepare($isReply ? 'SELECT user_id FROM comments WHERE id = :id LIMIT 1' : 'SELECT user_id FROM posts WHERE id = :id LIMIT 1');
        $owner->execute(['id' => $isReply ? $parentId : $postId]);
        notifyUser($database, (int) $owner->fetchColumn(), $userId, 'comment', $postId, $isReply ? 'replied to your comment.' : 'commented on your post.');
        $event = ['type' => $isReply ? 'comment_reply' : 'comment_created', 'post_id' => $postId, 'actor_id' => $userId, 'comment' => $comment, 'comment_count' => (int) $count->fetchColumn()];
        return ['event' => $event, 'result' => $event];
    }

    private function reactComment(int $userId, array $payload): array
    {
        $commentId = (int) ($payload['comment_id'] ?? 0);
        $database = db();
        $comment = getPostComment($database, $commentId, $userId);
        if (!$comment || !commentsCanViewPost($database, (int) $comment['post_id'], $userId)) throw new RuntimeException('This comment is unavailable.');
        $existing = $database->prepare('SELECT 1 FROM comment_reactions WHERE comment_id = :comment_id AND user_id = :user_id LIMIT 1');
        $existing->execute(['comment_id' => $commentId, 'user_id' => $userId]);
        if ($existing->fetch()) { $database->prepare('DELETE FROM comment_reactions WHERE comment_id = :comment_id AND user_id = :user_id')->execute(['comment_id' => $commentId, 'user_id' => $userId]); $reacted = false; }
        else { $database->prepare('INSERT INTO comment_reactions (comment_id, user_id) VALUES (:comment_id, :user_id)')->execute(['comment_id' => $commentId, 'user_id' => $userId]); $reacted = true; }
        $count = $database->prepare('SELECT COUNT(*) FROM comment_reactions WHERE comment_id = :comment_id');
        $count->execute(['comment_id' => $commentId]);
        $event = ['type' => 'comment_reaction', 'post_id' => (int) $comment['post_id'], 'comment_id' => $commentId, 'actor_id' => $userId, 'reacted' => $reacted, 'reaction_count' => (int) $count->fetchColumn()];
        return ['event' => $event, 'result' => $event];
    }

    private function assertPostAccess(PDO $database, int $postId, int $userId): void
    {
        if ($postId < 1 || !commentsCanViewPost($database, $postId, $userId)) throw new RuntimeException('This post is no longer available.');
    }

    private function sendActionResult(ConnectionInterface $connection, array $payload, array $result): void
    {
        $connection->send($this->encode(['type' => 'action_result', 'request_id' => $payload['request_id'] ?? null, 'success' => true, ...$result['result']]));
    }

    private function broadcast(array $event): void
    {
        $database = db();
        $postId = (int) ($event['post_id'] ?? 0);
        foreach ($this->clients as $connection) {
            $recipient = $this->clients[$connection];
            if ($postId < 1 || commentsCanViewPost($database, $postId, (int) $recipient['user_id'])) {
                $connection->send($this->encode($event));
            }
        }
    }

    public function onClose(ConnectionInterface $connection): void
    {
        if ($this->clients->contains($connection)) {
            $this->clients->detach($connection);
        }
    }

    public function onError(ConnectionInterface $connection, \Exception $exception): void
    {
        error_log($exception->getMessage());
        $connection->close();
    }

    private function authenticate(ConnectionInterface $connection): ?int
    {
        $cookieHeader = $connection->httpRequest->getHeader('Cookie');
        $cookies = [];
        foreach ($cookieHeader as $header) {
            foreach (explode(';', $header) as $cookie) {
                if (str_contains($cookie, '=')) {
                    [$name, $value] = explode('=', trim($cookie), 2);
                    $cookies[$name] = urldecode($value);
                }
            }
        }

        $sessionId = $cookies[session_name()] ?? '';
        if ($sessionId === '') {
            return null;
        }

        session_id($sessionId);
        if (session_status() !== PHP_SESSION_ACTIVE && !session_start()) {
            return null;
        }
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $authSessionId = (string) ($_SESSION['auth_session_id'] ?? '');
        session_write_close();

        if ($userId < 1 || $authSessionId === '') {
            return null;
        }

        $statement = db()->prepare(
            'SELECT user_id FROM sessions
             WHERE id = :session_id AND user_id = :user_id AND expires_at > NOW() LIMIT 1'
        );
        $statement->execute(['session_id' => $authSessionId, 'user_id' => $userId]);

        return $statement->fetchColumn() ? $userId : null;
    }

    private function conversationAccess(PDO $database, int $conversationId, int $userId): bool
    {
        $statement = $database->prepare(
            'SELECT 1 FROM conversation_participants
             WHERE conversation_id = :conversation_id AND user_id = :user_id LIMIT 1'
        );
        $statement->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);
        return (bool) $statement->fetchColumn();
    }

    private function encode(array $payload): string
    {
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }
}

$app = new App('localhost', (int) (env('WEBSOCKET_PORT', '8080') ?? 8080), '0.0.0.0');
$app->route('/gamershub', new GamerSocket(), ['*']);
$app->run();