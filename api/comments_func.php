<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

function commentsJsonResponse(array $payload, int $status = 200): void
{
	http_response_code($status);
	header('Content-Type: application/json; charset=UTF-8');
	echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
	exit;
}

function commentsRequireAuth(): int
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}

	if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
		commentsJsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
	}

	$statement = db()->prepare(
		'SELECT user_id
		 FROM sessions
		 WHERE id = :id
		   AND user_id = :user_id
		   AND expires_at > NOW()
		 LIMIT 1'
	);
	$statement->execute([
		'id' => $_SESSION['auth_session_id'],
		'user_id' => $_SESSION['user_id'],
	]);

	if (!$statement->fetch()) {
		commentsJsonResponse(['success' => false, 'message' => 'Authentication required.'], 401);
	}

	return (int) $_SESSION['user_id'];
}

function commentsRequireCsrf(): void
{
	$csrfToken = (string) ($_POST['csrf_token'] ?? '');
	if (empty($_SESSION['csrf_token']) || $csrfToken === '' || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
		commentsJsonResponse(['success' => false, 'message' => 'Your session expired. Please refresh and try again.'], 419);
	}
}

function commentsCanViewPost(PDO $database, int $postId, int $userId): bool
{
	$statement = $database->prepare(
		'SELECT p.id
		 FROM posts p
		 WHERE p.id = :post_id
		   AND p.status = \'published\'
		   AND (
			   p.user_id = :viewer_id
			   OR p.visibility = \'public\'
			   OR (p.visibility = \'followers\' AND EXISTS (
				   SELECT 1 FROM followers f
				   WHERE f.follower_id = :viewer_follower_id AND f.following_id = p.user_id
			   ))
			   OR (p.visibility = \'friends\' AND EXISTS (
				   SELECT 1
				   FROM followers fo
				   INNER JOIN followers fi
					   ON fi.follower_id = fo.following_id
					  AND fi.following_id = fo.follower_id
				   WHERE fo.follower_id = :viewer_friend_id AND fo.following_id = p.user_id
			   ))
		   )
		 LIMIT 1'
	);
	$statement->execute([
		'post_id' => $postId,
		'viewer_id' => $userId,
		'viewer_follower_id' => $userId,
		'viewer_friend_id' => $userId,
	]);

	return (bool) $statement->fetch();
}

function getPostComments(PDO $database, int $postId, int $viewerId = 0): array
{
	$statement = $database->prepare(
		'SELECT
			c.id,
			c.post_id,
			c.parent_id,
			c.content,
			c.created_at,
			u.username,
			up.display_name,
			up.avatar_url,
			replied_user.username AS reply_to_username,
			replied_profile.display_name AS reply_to_display_name,
			COUNT(DISTINCT cr.user_id) AS reaction_count,
			MAX(CASE WHEN cr.user_id = :viewer_id THEN 1 ELSE 0 END) AS viewer_reacted,
			(SELECT COUNT(*) FROM comments replies WHERE replies.parent_id = c.id AND replies.status = \'published\') AS reply_count
		 FROM comments c
		 INNER JOIN users u ON u.id = c.user_id AND u.status = \'active\'
		 LEFT JOIN user_profiles up ON up.user_id = u.id
		 LEFT JOIN comments replied_comment ON replied_comment.id = c.parent_id
		 LEFT JOIN users replied_user ON replied_user.id = replied_comment.user_id
		 LEFT JOIN user_profiles replied_profile ON replied_profile.user_id = replied_user.id
		 LEFT JOIN comment_reactions cr ON cr.comment_id = c.id
		 WHERE c.post_id = :post_id
		   AND c.status = \'published\'
		 GROUP BY c.id, c.post_id, c.parent_id, c.content, c.created_at, u.username, up.display_name, up.avatar_url, replied_user.username, replied_profile.display_name
		 ORDER BY c.created_at ASC, c.id ASC'
	);
	$statement->execute([
		'post_id' => $postId,
		'viewer_id' => $viewerId,
	]);

	return $statement->fetchAll();
}

function getPostComment(PDO $database, int $commentId, int $viewerId = 0): ?array
{
	$comments = $database->prepare(
		'SELECT
			c.id,
			c.post_id,
			c.parent_id,
			c.content,
			c.created_at,
			u.username,
			up.display_name,
			up.avatar_url,
			replied_user.username AS reply_to_username,
			replied_profile.display_name AS reply_to_display_name,
			COUNT(DISTINCT cr.user_id) AS reaction_count,
			MAX(CASE WHEN cr.user_id = :viewer_id THEN 1 ELSE 0 END) AS viewer_reacted,
			0 AS reply_count
		 FROM comments c
		 INNER JOIN users u ON u.id = c.user_id AND u.status = \'active\'
		 LEFT JOIN user_profiles up ON up.user_id = u.id
		 LEFT JOIN comments replied_comment ON replied_comment.id = c.parent_id
		 LEFT JOIN users replied_user ON replied_user.id = replied_comment.user_id
		 LEFT JOIN user_profiles replied_profile ON replied_profile.user_id = replied_user.id
		 LEFT JOIN comment_reactions cr ON cr.comment_id = c.id
		 WHERE c.id = :comment_id
		   AND c.status = \'published\'
		 GROUP BY c.id, c.post_id, c.parent_id, c.content, c.created_at, u.username, up.display_name, up.avatar_url, replied_user.username, replied_profile.display_name
		 LIMIT 1'
	);
	$comments->execute([
		'comment_id' => $commentId,
		'viewer_id' => $viewerId,
	]);
	$comment = $comments->fetch();

	return $comment ?: null;
}

function notifyUser(PDO $database, int $recipientId, int $actorId, string $type, int $postId, string $message): void
{
	if ($recipientId < 1 || $recipientId === $actorId) {
		return;
	}

	$statement = $database->prepare(
		'INSERT INTO notifications (user_id, actor_id, type, post_id, message)
		 VALUES (:user_id, :actor_id, :type, :post_id, :message)'
	);
	$statement->execute([
		'user_id' => $recipientId,
		'actor_id' => $actorId,
		'type' => $type,
		'post_id' => $postId > 0 ? $postId : null,
		'message' => $message,
	]);
}

function handleCommentsApiRequest(): void
{
	$userId = commentsRequireAuth();
	$database = db();
	$action = (string) ($_POST['action'] ?? $_GET['action'] ?? 'list');
	$postId = (int) ($_POST['post_id'] ?? $_GET['post_id'] ?? 0);

	if ($postId < 1 || !commentsCanViewPost($database, $postId, $userId)) {
		commentsJsonResponse(['success' => false, 'message' => 'This post is not available.'], 404);
	}

	if ($action === 'list') {
		$comments = getPostComments($database, $postId, $userId);
		commentsJsonResponse(['success' => true, 'comments' => $comments, 'comment_count' => count($comments)]);
	}

	commentsRequireCsrf();

	if ($action === 'create' || $action === 'reply') {
		$content = trim((string) ($_POST['content'] ?? ''));
		$parentId = (int) ($_POST['parent_id'] ?? 0);
		if ($content === '' || mb_strlen($content) > 2000) {
			commentsJsonResponse(['success' => false, 'message' => 'Comments must contain between 1 and 2,000 characters.'], 422);
		}

		if ($action === 'create') {
			$parentId = 0;
		} elseif ($parentId < 1) {
			commentsJsonResponse(['success' => false, 'message' => 'Choose a comment to reply to.'], 422);
		}

		if ($parentId > 0) {
			$parentStatement = $database->prepare(
				'SELECT id FROM comments WHERE id = :id AND post_id = :post_id AND status = \'published\' LIMIT 1'
			);
			$parentStatement->execute(['id' => $parentId, 'post_id' => $postId]);
			if (!$parentStatement->fetch()) {
				commentsJsonResponse(['success' => false, 'message' => 'The comment you are replying to is unavailable.'], 404);
			}
		}

		$insert = $database->prepare(
			'INSERT INTO comments (post_id, user_id, parent_id, content, status)
			 VALUES (:post_id, :user_id, :parent_id, :content, \'published\')'
		);
		$insert->execute([
			'post_id' => $postId,
			'user_id' => $userId,
			'parent_id' => $parentId > 0 ? $parentId : null,
			'content' => $content,
		]);
		$commentId = (int) $database->lastInsertId();
		$comment = getPostComment($database, $commentId, $userId);
		$count = $database->prepare('SELECT COUNT(*) FROM comments WHERE post_id = :post_id AND status = \'published\'');
		$count->execute(['post_id' => $postId]);

		if ($action === 'create') {
			$postOwner = $database->prepare(
				'SELECT user_id FROM posts WHERE id = :post_id AND status = \'published\' LIMIT 1'
			);
			$postOwner->execute(['post_id' => $postId]);
			$owner = $postOwner->fetchColumn();
			if ($owner !== false) {
				notifyUser($database, (int) $owner, $userId, 'comment', $postId, 'commented on your post.');
			}
		} else {
			$replyOwner = $database->prepare(
				'SELECT c.user_id FROM comments c WHERE c.id = :comment_id AND c.status = \'published\' LIMIT 1'
			);
			$replyOwner->execute(['comment_id' => $parentId]);
			$owner = $replyOwner->fetchColumn();
			if ($owner !== false) {
				notifyUser($database, (int) $owner, $userId, 'comment', $postId, 'replied to your comment.');
			}
		}

		commentsJsonResponse(['success' => true, 'comment' => $comment, 'comment_count' => (int) $count->fetchColumn()]);
	}

	if ($action === 'react') {
		$commentId = (int) ($_POST['comment_id'] ?? 0);
		$comment = getPostComment($database, $commentId, $userId);
		if (!$comment || (int) $comment['post_id'] !== $postId) {
			commentsJsonResponse(['success' => false, 'message' => 'This comment is unavailable.'], 404);
		}

		$existing = $database->prepare('SELECT comment_id FROM comment_reactions WHERE comment_id = :comment_id AND user_id = :user_id LIMIT 1');
		$existing->execute(['comment_id' => $commentId, 'user_id' => $userId]);
		if ($existing->fetch()) {
			$database->prepare('DELETE FROM comment_reactions WHERE comment_id = :comment_id AND user_id = :user_id')
				->execute(['comment_id' => $commentId, 'user_id' => $userId]);
			$reacted = false;
		} else {
			$database->prepare('INSERT INTO comment_reactions (comment_id, user_id) VALUES (:comment_id, :user_id)')
				->execute(['comment_id' => $commentId, 'user_id' => $userId]);
			$reacted = true;
		}

		$count = $database->prepare('SELECT COUNT(*) FROM comment_reactions WHERE comment_id = :comment_id');
		$count->execute(['comment_id' => $commentId]);
		commentsJsonResponse(['success' => true, 'reacted' => $reacted, 'reaction_count' => (int) $count->fetchColumn()]);
	}

	commentsJsonResponse(['success' => false, 'message' => 'Invalid comment action.'], 422);
}

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === realpath(__FILE__)) {
	try {
		handleCommentsApiRequest();
	} catch (Throwable $exception) {
		commentsJsonResponse(['success' => false, 'message' => 'Unable to process the comment request.'], 500);
	}
}
