<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function notificationResponse(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

set_exception_handler(static function (Throwable $exception): never {
    error_log($exception->getMessage());
    notificationResponse(['error' => 'The notification service is temporarily unavailable.'], 500);
});

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    notificationResponse(['error' => 'Authentication required.'], 401);
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
    notificationResponse(['error' => 'Session expired.'], 401);
}

$userId = (int) $_SESSION['user_id'];
$action = (string) ($_GET['action'] ?? $_POST['action'] ?? 'list');

if ($action === 'mark_read') {
    $notificationId = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
    if ($notificationId < 1) {
        notificationResponse(['error' => 'Notification not found.'], 422);
    }

    $statement = $database->prepare(
        'UPDATE notifications SET is_read = TRUE
         WHERE id = :id AND user_id = :user_id'
    );
    $statement->execute(['id' => $notificationId, 'user_id' => $userId]);
    notificationResponse(['ok' => true]);
}

if ($action === 'mark_all_read') {
    $statement = $database->prepare(
        'UPDATE notifications SET is_read = TRUE
         WHERE user_id = :user_id AND is_read = FALSE'
    );
    $statement->execute(['user_id' => $userId]);
    notificationResponse(['ok' => true]);
}

if ($action !== 'list') {
    notificationResponse(['error' => 'Unsupported notification action.'], 422);
}

$limit = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
$statement = $database->prepare(
    'SELECT n.id, n.type, n.post_id, n.message, n.is_read, n.created_at,
            p.display_name AS actor_name, p.avatar_url AS actor_avatar
     FROM notifications n
     LEFT JOIN user_profiles p ON p.user_id = n.actor_id
     WHERE n.user_id = :user_id
     ORDER BY n.created_at DESC, n.id DESC
     LIMIT ' . $limit
);
$statement->execute(['user_id' => $userId]);

$notifications = array_map(static function (array $row): array {
    return [
        'id' => (int) $row['id'],
        'type' => $row['type'],
        'post_id' => $row['post_id'] !== null ? (int) $row['post_id'] : null,
        'message' => $row['message'] ?: 'You have new activity on GamersHUB.',
        'is_read' => (bool) $row['is_read'],
        'created_at' => $row['created_at'],
        'actor_name' => $row['actor_name'],
        'actor_avatar' => $row['actor_avatar'],
    ];
}, $statement->fetchAll());

$unreadStatement = $database->prepare(
    'SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = FALSE'
);
$unreadStatement->execute(['user_id' => $userId]);

notificationResponse([
    'notifications' => $notifications,
    'unread_count' => (int) $unreadStatement->fetchColumn(),
]);