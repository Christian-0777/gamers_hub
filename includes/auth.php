<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/urls.php';

function destroyCurrentSession(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $cookieParameters = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $cookieParameters['path'],
            $cookieParameters['domain'] ?? '',
            $cookieParameters['secure'],
            $cookieParameters['httponly']
        );
    }

    session_destroy();
}

function redirectToLogin(): void
{
    header('Location: ' . appUrl('login'));
    exit;
}

function requireAuth(PDO $pdo): int
{
    gamers_session_started();

    if (empty($_SESSION['user_id']) || empty($_SESSION['session_token'])) {
        destroyCurrentSession();
        redirectToLogin();
    }

    $userId = (int) $_SESSION['user_id'];
    $tokenHash = hash('sha256', (string) $_SESSION['session_token']);

    $statement = $pdo->prepare(
        'SELECT id, user_id, expires_at, revoked_at
         FROM user_sessions
         WHERE user_id = :user_id
           AND session_token_hash = :token_hash
         LIMIT 1'
    );
    $statement->execute([
        'user_id' => $userId,
        'token_hash' => $tokenHash,
    ]);

    $session = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        destroyCurrentSession();
        redirectToLogin();
    }

    if ($session['revoked_at'] !== null) {
        destroyCurrentSession();
        redirectToLogin();
    }

    if (!empty($session['expires_at']) && strtotime((string) $session['expires_at']) <= time()) {
        destroyCurrentSession();
        redirectToLogin();
    }

    $pdo->prepare(
        'UPDATE user_sessions
         SET last_activity_at = NOW()
         WHERE id = :id'
    )->execute([
        'id' => $session['id'],
    ]);

    return $userId;
}
