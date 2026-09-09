<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$userId = (int) ($_SESSION['user_id'] ?? 0);
$currentSessionId = (string) ($_SESSION['auth_session_id'] ?? '');
$sessionId = trim((string) ($_POST['session_id'] ?? ''));
$csrfToken = (string) ($_POST['csrf_token'] ?? '');

if ($userId < 1 || $currentSessionId === '' || $sessionId === ''
    || empty($_SESSION['csrf_token'])
    || $csrfToken === ''
    || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    http_response_code(403);
    exit('Unable to log out this device.');
}

$database = db();
$sessionStatement = $database->prepare(
    'DELETE FROM sessions WHERE id = :session_id AND user_id = :user_id'
);
$sessionStatement->execute([
    'session_id' => $sessionId,
    'user_id' => $userId,
]);

$historyStatement = $database->prepare(
    'UPDATE user_login_history
     SET action = "logout"
     WHERE user_id = :user_id AND session_id = :session_id'
);
$historyStatement->execute([
    'user_id' => $userId,
    'session_id' => $sessionId,
]);

if ($sessionId === $currentSessionId) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . appUrl('login'));
    exit;
}

header('Location: ' . appUrl('settings') . '?tab=security');
exit;