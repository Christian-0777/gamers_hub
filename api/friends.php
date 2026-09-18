<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

header('Content-Type: application/json; charset=utf-8');

function friendsResponse(array $payload, int $status = 200): never
{
	http_response_code($status);
	echo json_encode($payload, JSON_UNESCAPED_SLASHES);
	exit;
}

set_exception_handler(static function (Throwable $exception): never {
	error_log($exception->getMessage());
	friendsResponse(['error' => 'The friends service is temporarily unavailable.'], 500);
});

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
	friendsResponse(['error' => 'Authentication required.'], 401);
}

$database = db();
$sessionStatement = $database->prepare(
	'SELECT user_id FROM sessions
	 WHERE id = :session_id AND user_id = :user_id AND expires_at > NOW()
	 LIMIT 1'
);
$sessionStatement->execute([
	'session_id' => $_SESSION['auth_session_id'],
	'user_id' => $_SESSION['user_id'],
]);

if (!$sessionStatement->fetch()) {
	friendsResponse(['error' => 'Session expired.'], 401);
}

$userId = (int) $_SESSION['user_id'];
$action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'list');

	$profileSelect = 'SELECT u.id, u.username, p.display_name, p.avatar_url, p.online_status,
						 GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR \', \') AS games
				  FROM users u
				  INNER JOIN user_profiles p ON p.user_id = u.id
				  LEFT JOIN user_games ug ON ug.user_id = u.id AND ug.status IN (\'playing\', \'favorite\')
				  LEFT JOIN game_catalog g ON g.id = ug.game_id';

function profilePayload(array $row, string $relationship = 'none'): array
{
	$name = (string) ($row['display_name'] ?: $row['username']);
	return [
		'id' => (int) $row['id'],
		'username' => $row['username'],
		'name' => $name,
		'initials' => strtoupper(substr($name, 0, 1) . substr((string) $row['username'], 0, 1)),
		'avatar' => $row['avatar_url'],
		'online_status' => $row['online_status'] ?: 'offline',
		'games' => $row['games'] ? explode(', ', $row['games']) : [],
		'relationship' => $relationship,
	];
}

function notify(PDO $database, int $recipientId, int $actorId, string $message): void
{
	$statement = $database->prepare(
		'INSERT INTO notifications (user_id, actor_id, type, message)
		 VALUES (:user_id, :actor_id, \'follow\', :message)'
	);
	$statement->execute([
		'user_id' => $recipientId,
		'actor_id' => $actorId,
		'message' => $message,
	]);
}

if ($action === 'list') {
	$query = $profileSelect . '
		WHERE u.id <> :user_id AND u.status = \'active\'
		GROUP BY u.id, u.username, p.display_name, p.avatar_url, p.online_status
		ORDER BY p.display_name ASC';
	$statement = $database->prepare($query);
	$statement->execute(['user_id' => $userId]);
	$allUsers = $statement->fetchAll();

	$friendStatement = $database->prepare(
		'SELECT f1.following_id
		 FROM followers f1 INNER JOIN followers f2
		   ON f2.follower_id = f1.following_id AND f2.following_id = f1.follower_id
		 WHERE f1.follower_id = :user_id'
	);
	$friendStatement->execute(['user_id' => $userId]);
	$friendIds = array_fill_keys(array_map('intval', $friendStatement->fetchAll(PDO::FETCH_COLUMN)), true);

	$incomingStatement = $database->prepare(
		'SELECT f.follower_id FROM followers f
		 WHERE f.following_id = :user_id
		   AND NOT EXISTS (SELECT 1 FROM followers r WHERE r.follower_id = :user_id_reverse AND r.following_id = f.follower_id)'
	);
	$incomingStatement->execute(['user_id' => $userId, 'user_id_reverse' => $userId]);
	$incomingIds = array_fill_keys(array_map('intval', $incomingStatement->fetchAll(PDO::FETCH_COLUMN)), true);

	$sentStatement = $database->prepare(
		'SELECT f.following_id FROM followers f
		 WHERE f.follower_id = :user_id
		   AND NOT EXISTS (SELECT 1 FROM followers r WHERE r.follower_id = f.following_id AND r.following_id = :user_id_reverse)'
	);
	$sentStatement->execute(['user_id' => $userId, 'user_id_reverse' => $userId]);
	$sentIds = array_fill_keys(array_map('intval', $sentStatement->fetchAll(PDO::FETCH_COLUMN)), true);

	$friends = $requests = $sent = $gamers = [];
	foreach ($allUsers as $row) {
		$id = (int) $row['id'];
		$relationship = isset($friendIds[$id]) ? 'friend' : (isset($incomingIds[$id]) ? 'incoming' : (isset($sentIds[$id]) ? 'sent' : 'none'));
		$profile = profilePayload($row, $relationship);
		if ($relationship === 'friend') $friends[] = $profile;
		elseif ($relationship === 'incoming') $requests[] = $profile;
		elseif ($relationship === 'sent') $sent[] = $profile;
		else $gamers[] = $profile;
	}

	friendsResponse(['friends' => $friends, 'requests' => $requests, 'sent' => $sent, 'gamers' => $gamers]);
}

$targetId = (int) ($_POST['user_id'] ?? $_GET['user_id'] ?? 0);
if ($targetId < 1 || $targetId === $userId) {
	friendsResponse(['error' => 'Choose another gamer.'], 422);
}

$targetStatement = $database->prepare('SELECT id FROM users WHERE id = :id AND status = \'active\' LIMIT 1');
$targetStatement->execute(['id' => $targetId]);
if (!$targetStatement->fetch()) {
	friendsResponse(['error' => 'Gamer not found.'], 404);
}

if ($action === 'request') {
	$existing = $database->prepare('SELECT 1 FROM followers WHERE follower_id = :from_id AND following_id = :to_id LIMIT 1');
	$existing->execute(['from_id' => $userId, 'to_id' => $targetId]);
	$alreadyFollowing = (bool) $existing->fetch();
	if (!$alreadyFollowing) {
		$reverse = $database->prepare('SELECT 1 FROM followers WHERE follower_id = :from_id AND following_id = :to_id LIMIT 1');
		$reverse->execute(['from_id' => $targetId, 'to_id' => $userId]);
		if ($reverse->fetch()) {
			friendsResponse(['error' => 'This gamer already sent you a request.'], 409);
		}
	}
	$database->beginTransaction();
	try {
		if (!$alreadyFollowing) {
			$insert = $database->prepare('INSERT INTO followers (follower_id, following_id) VALUES (:from_id, :to_id)');
			$insert->execute(['from_id' => $userId, 'to_id' => $targetId]);
			notify($database, $targetId, $userId, 'sent you a friend request.');
		}
		$database->commit();
		friendsResponse(['ok' => true]);
	} catch (Throwable $exception) {
		$database->rollBack();
		throw $exception;
	}
}

if ($action === 'accept') {
	$incoming = $database->prepare('SELECT 1 FROM followers WHERE follower_id = :from_id AND following_id = :to_id LIMIT 1');
	$incoming->execute(['from_id' => $targetId, 'to_id' => $userId]);
	if (!$incoming->fetch()) friendsResponse(['error' => 'Friend request not found.'], 404);
	$database->beginTransaction();
	try {
		$insert = $database->prepare('INSERT IGNORE INTO followers (follower_id, following_id) VALUES (:from_id, :to_id)');
		$insert->execute(['from_id' => $userId, 'to_id' => $targetId]);
		notify($database, $targetId, $userId, 'accepted your friend request.');
		$database->commit();
		friendsResponse(['ok' => true]);
	} catch (Throwable $exception) {
		if ($database->inTransaction()) $database->rollBack();
		throw $exception;
	}
}

if ($action === 'decline' || $action === 'cancel' || $action === 'unfriend') {
	$where = $action === 'decline' ? 'follower_id = :target_id AND following_id = :user_id' : ($action === 'cancel' ? 'follower_id = :user_id AND following_id = :target_id' : '(follower_id = :user_id AND following_id = :target_id) OR (follower_id = :target_id AND following_id = :user_id)');
	$statement = $database->prepare('DELETE FROM followers WHERE ' . $where);
	$statement->execute(['user_id' => $userId, 'target_id' => $targetId]);
	friendsResponse(['ok' => true]);
}

friendsResponse(['error' => 'Unsupported friend action.'], 422);
