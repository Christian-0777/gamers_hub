<?php

declare(strict_types=1);

function gamers_session_started(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $isSecureRequest = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    $isLocalhost = isset($_SERVER['HTTP_HOST'])
        && preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(?::\d+)?$/i', (string) $_SERVER['HTTP_HOST']) === 1;

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isSecureRequest && !$isLocalhost,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

gamers_session_started();
