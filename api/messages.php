<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

set_error_handler(static function (int $severity, string $message, string $file, int $line): never {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function messageResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

set_exception_handler(static function (Throwable $exception): never {
    error_log($exception->getMessage());
    messageResponse(['error' => 'The messaging service is temporarily unavailable.'], 500);
});

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    messageResponse(['error' => 'Authentication required.'], 401);
}

$database = db();
$session = $database->prepare(
    'SELECT user_id FROM sessions
     WHERE id = :session_id AND user_id = :user_id AND expires_at > NOW()
     LIMIT 1'
);
$session->execute([
    'session_id' => $_SESSION['auth_session_id'],
    'user_id' => $_SESSION['user_id'],
]);

if (!$session->fetch()) {
    messageResponse(['error' => 'Session expired.'], 401);
}

$userId = (int) $_SESSION['user_id'];
$action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'conversations');

if ($action === 'ping') {
    messageResponse(['ok' => true, 'server_time' => microtime(true)]);
}

function conversationAccess(PDO $database, int $conversationId, int $userId): bool
{
    $statement = $database->prepare(
        'SELECT 1 FROM conversation_participants
         WHERE conversation_id = :conversation_id AND user_id = :user_id LIMIT 1'
    );
    $statement->execute(['conversation_id' => $conversationId, 'user_id' => $userId]);
    return (bool) $statement->fetchColumn();
}

if ($action === 'conversations') {
    $statement = $database->prepare(
        'SELECT c.id, c.is_group, c.title, c.updated_at,
                p.user_id AS participant_id, p.display_name, p.avatar_url, p.online_status,
                last_message.body AS last_body, last_message.created_at AS last_message_at,
                COALESCE(unread.unread_count, 0) AS unread_count
         FROM conversation_participants mine
         INNER JOIN conversations c ON c.id = mine.conversation_id
         LEFT JOIN conversation_participants other
            ON other.conversation_id = c.id AND other.user_id <> :user_id
         LEFT JOIN user_profiles p ON p.user_id = other.user_id
         LEFT JOIN messages last_message ON last_message.id = (
            SELECT m.id FROM messages m WHERE m.conversation_id = c.id
            ORDER BY m.created_at DESC, m.id DESC LIMIT 1
         )
         LEFT JOIN (
            SELECT cp.conversation_id, COUNT(m.id) AS unread_count
            FROM conversation_participants cp
            LEFT JOIN messages m ON m.conversation_id = cp.conversation_id
                AND m.id > COALESCE(cp.last_read_message_id, 0)
                AND m.sender_id <> cp.user_id
            WHERE cp.user_id = :unread_user_id
            GROUP BY cp.conversation_id
         ) unread ON unread.conversation_id = c.id
         WHERE mine.user_id = :mine_user_id
         ORDER BY COALESCE(last_message.created_at, c.updated_at) DESC, c.id DESC'
    );
    $statement->execute([
        'user_id' => $userId,
        'unread_user_id' => $userId,
        'mine_user_id' => $userId,
    ]);

    $conversations = [];
    foreach ($statement->fetchAll() as $row) {
        $conversations[] = [
            'id' => (int) $row['id'],
            'name' => $row['is_group'] ? ($row['title'] ?: 'Group conversation') : ($row['display_name'] ?: 'Gamer'),
            'avatar' => $row['avatar_url'],
            'status' => $row['is_group'] ? 'Group conversation' : ucfirst((string) ($row['online_status'] ?: 'offline')),
            'online' => $row['online_status'] === 'online',
            'preview' => $row['last_body'] ?: 'Start a conversation',
            'updated_at' => $row['last_message_at'] ?: $row['updated_at'],
            'unread' => (int) $row['unread_count'],
        ];
    }

    messageResponse(['conversations' => $conversations]);
}

$conversationId = (int) ($_GET['conversation_id'] ?? $_POST['conversation_id'] ?? 0);
if ($conversationId < 1 || !conversationAccess($database, $conversationId, $userId)) {
    messageResponse(['error' => 'Conversation not found.'], 404);
}

if ($action === 'send') {
    $body = trim((string) ($_POST['body'] ?? ''));
    if ($body === '' || strlen($body) > 4000) {
        messageResponse(['error' => 'Message must be between 1 and 4000 characters.'], 422);
    }

    $statement = $database->prepare(
        'INSERT INTO messages (conversation_id, sender_id, body) VALUES (:conversation_id, :sender_id, :body)'
    );
    $statement->execute(['conversation_id' => $conversationId, 'sender_id' => $userId, 'body' => $body]);
    $database->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = :id')->execute(['id' => $conversationId]);
    $messageId = (int) $database->lastInsertId();
    $database->prepare(
        'UPDATE conversation_participants SET last_read_message_id = :message_id
         WHERE conversation_id = :conversation_id AND user_id = :user_id'
    )->execute(['message_id' => $messageId, 'conversation_id' => $conversationId, 'user_id' => $userId]);

    messageResponse(['message' => ['id' => $messageId, 'body' => $body, 'sender_id' => $userId, 'created_at' => date('Y-m-d H:i:s')]], 201);
}

$afterId = max(0, (int) ($_GET['after_id'] ?? 0));
$statement = $database->prepare(
    'SELECT m.id, m.body, m.sender_id, m.created_at, p.display_name AS sender_name
     FROM messages m INNER JOIN user_profiles p ON p.user_id = m.sender_id
     WHERE m.conversation_id = :conversation_id AND m.id > :after_id
     ORDER BY m.created_at ASC, m.id ASC LIMIT 200'
);
$statement->execute(['conversation_id' => $conversationId, 'after_id' => $afterId]);

$database->prepare(
    'UPDATE conversation_participants SET last_read_message_id = COALESCE(
        (SELECT MAX(id) FROM messages WHERE conversation_id = :read_conversation_id), last_read_message_id
     ) WHERE conversation_id = :conversation_id_update AND user_id = :user_id'
)->execute(['read_conversation_id' => $conversationId, 'conversation_id_update' => $conversationId, 'user_id' => $userId]);

messageResponse(['messages' => array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'body' => $row['body'],
        'sender_id' => (int) $row['sender_id'],
        'sender_name' => $row['sender_name'],
        'created_at' => $row['created_at'],
    ];
}, $statement->fetchAll())]);