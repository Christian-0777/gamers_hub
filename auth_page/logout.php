<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/urls.php';

gamers_session_started();

if (!empty($_SESSION['session_token'])) {
    try {
        $statement = db()->prepare(
            'UPDATE user_sessions
             SET revoked_at = NOW()
             WHERE session_token_hash = :token_hash'
        );
        $statement->execute([
            'token_hash' => hash('sha256', (string) $_SESSION['session_token']),
        ]);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
    }
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $cookieParameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $cookieParameters['path'], $cookieParameters['domain'] ?? '', $cookieParameters['secure'], $cookieParameters['httponly']);
}

session_destroy();
header('Location: ' . appUrl());
exit;