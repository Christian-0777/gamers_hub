<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!empty($_SESSION['auth_session_id']) && !empty($_SESSION['user_id'])) {
    try {
        $statement = db()->prepare(
            'DELETE FROM sessions WHERE id = :id AND user_id = :user_id'
        );
        $statement->execute([
            'id' => $_SESSION['auth_session_id'],
            'user_id' => $_SESSION['user_id'],
        ]);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookieParameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookieParameters['path'], $cookieParameters['domain'], $cookieParameters['secure'], $cookieParameters['httponly']);
}

session_destroy();
header('Location: ' . appUrl());
exit;