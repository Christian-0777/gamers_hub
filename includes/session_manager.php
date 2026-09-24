<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/session.php';

function getDeviceName(?string $userAgent): string
{
    if (!$userAgent) {
        return 'Unknown Device';
    }

    if (stripos($userAgent, 'Android') !== false) {
        return 'Android Device';
    }

    if (stripos($userAgent, 'iPhone') !== false || stripos($userAgent, 'iPad') !== false) {
        return 'Apple Device';
    }

    if (stripos($userAgent, 'Windows') !== false) {
        return 'Windows PC';
    }

    if (stripos($userAgent, 'Macintosh') !== false) {
        return 'Mac';
    }

    if (stripos($userAgent, 'Linux') !== false) {
        return 'Linux PC';
    }

    return 'Unknown Device';
}

function createUserSession(PDO $pdo, int $userId): void
{
    gamers_session_started();
    session_regenerate_id(true);

    $sessionToken = bin2hex(random_bytes(32));
    $sessionTokenHash = hash('sha256', $sessionToken);
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $deviceName = getDeviceName($userAgent);

    $_SESSION['user_id'] = $userId;
    $_SESSION['session_token'] = $sessionToken;
    $_SESSION['authenticated'] = true;

    $statement = $pdo->prepare(
        'INSERT INTO user_sessions (
            user_id,
            session_token_hash,
            ip_address,
            user_agent,
            device_name,
            created_at,
            last_activity_at,
            expires_at
        ) VALUES (
            :user_id,
            :session_token_hash,
            :ip_address,
            :user_agent,
            :device_name,
            NOW(),
            NOW(),
            DATE_ADD(NOW(), INTERVAL 30 DAY)
        )'
    );

    $statement->execute([
        'user_id' => $userId,
        'session_token_hash' => $sessionTokenHash,
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'device_name' => $deviceName,
    ]);

    $_SESSION['auth_session_id'] = (int) $pdo->lastInsertId();
}
