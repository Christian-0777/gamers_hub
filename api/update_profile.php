<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';
require_once __DIR__ . '/../config/request_context.php';
require_once __DIR__ . '/photo_compressor.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    http_response_code(401);
    header('Location: ' . appUrl('login'));
    exit;
}

$database = db();
$sessionStatement = $database->prepare(
    'SELECT user_id FROM sessions WHERE id = :id AND user_id = :user_id AND expires_at > NOW() LIMIT 1'
);
$sessionStatement->execute([
    'id' => $_SESSION['auth_session_id'],
    'user_id' => $_SESSION['user_id'],
]);

if (!$sessionStatement->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['auth_session_id']);
    http_response_code(401);
    header('Location: ' . appUrl('login'));
    exit;
}

if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(419);
    header('Location: ' . appUrl('settings') . '?tab=' . urlencode((string) ($_POST['tab'] ?? 'account')));
    exit;
}

$userId = (int) $_SESSION['user_id'];
$tab = in_array((string) ($_POST['tab'] ?? 'account'), ['account', 'privacy', 'notifications', 'security', 'social'], true)
    ? (string) $_POST['tab']
    : 'account';

$cleanString = function ($value) {
    $trimmed = trim((string) $value);
    return $trimmed === '' ? null : $trimmed;
};

$normalizeSocialUrl = function ($value): ?string {
    $url = trim((string) $value);
    if ($url === '') {
        return null;
    }

    if (!preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
        $url = 'https://' . $url;
    }

    return $url;
};

$avatarUpload = $_FILES['avatar_file'] ?? null;
$coverUpload = $_FILES['cover_file'] ?? null;

$currentUserStatement = $database->prepare('SELECT avatar_url, cover_url FROM user_profiles WHERE user_id = :user_id LIMIT 1');
$currentUserStatement->execute(['user_id' => $userId]);
$currentProfile = $currentUserStatement->fetch();

$avatarUrlValue = $currentProfile['avatar_url'] ?? null;
$coverUrlValue = $currentProfile['cover_url'] ?? null;

if (($avatarUpload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $avatarResult = compressUploadedImage($avatarUpload, 'uploads/profile', 'profile_' . $userId);
    $avatarUrlValue = $avatarResult['url'];
}

if (($coverUpload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
    $coverResult = compressUploadedImage($coverUpload, 'uploads/cover', 'cover_' . $userId);
    $coverUrlValue = $coverResult['url'];
}

$fields = [
    'display_name' => $cleanString($_POST['display_name'] ?? ''),
    'bio' => $cleanString($_POST['bio'] ?? ''),
    'social_discord' => $normalizeSocialUrl($_POST['social_discord'] ?? ''),
    'social_tiktok' => $normalizeSocialUrl($_POST['social_tiktok'] ?? ''),
    'social_facebook' => $normalizeSocialUrl($_POST['social_facebook'] ?? ''),
    'social_twitch' => $normalizeSocialUrl($_POST['social_twitch'] ?? ''),
    'social_kick' => $normalizeSocialUrl($_POST['social_kick'] ?? ''),
    'social_github' => $normalizeSocialUrl($_POST['social_github'] ?? ''),
    'social_spotify' => $normalizeSocialUrl($_POST['social_spotify'] ?? ''),
    'social_youtube' => $normalizeSocialUrl($_POST['social_youtube'] ?? ''),
    'social_apple_music' => $normalizeSocialUrl($_POST['social_apple_music'] ?? ''),
    'social_steam' => $normalizeSocialUrl($_POST['social_steam'] ?? ''),
    'social_x' => $normalizeSocialUrl($_POST['social_x'] ?? ''),
    'avatar_url' => $avatarUrlValue,
    'cover_url' => $coverUrlValue,
    'gaming_style' => isset($_POST['gaming_style']) && $_POST['gaming_style'] !== '' ? $_POST['gaming_style'] : null,
    'profile_visibility' => isset($_POST['profile_visibility']) && $_POST['profile_visibility'] !== '' ? $_POST['profile_visibility'] : null,
    'who_can_message' => isset($_POST['who_can_message']) && $_POST['who_can_message'] !== '' ? $_POST['who_can_message'] : null,
    'preferred_voice_chat' => isset($_POST['preferred_voice_chat']) ? 1 : null,
    'email_notifications' => isset($_POST['email_notifications']) ? 1 : null,
    'push_notifications' => isset($_POST['push_notifications']) ? 1 : null,
    'social_notifications' => isset($_POST['social_notifications']) ? 1 : null,
    'two_factor_enabled' => isset($_POST['two_factor_enabled']) ? 1 : null,
];

try {
    $database->beginTransaction();

    $updateParts = [];
    $params = ['user_id' => $userId];

    foreach ($fields as $field => $value) {
        if ($value === null) {
            continue;
        }

        if (in_array($field, ['preferred_voice_chat', 'email_notifications', 'push_notifications', 'social_notifications', 'two_factor_enabled'], true)) {
            $updateParts[] = "{$field} = :{$field}";
            $params[$field] = (int) $value;
            continue;
        }

        if ($field === 'display_name' && strlen((string) $value) > 50) {
            throw new InvalidArgumentException('Display name is too long.');
        }

        if ($field === 'bio' && strlen((string) $value) > 500) {
            throw new InvalidArgumentException('Bio is too long.');
        }

        if (str_starts_with($field, 'social_')) {
            $urlParts = parse_url((string) $value);
            $validScheme = in_array(strtolower((string) ($urlParts['scheme'] ?? '')), ['http', 'https'], true);
            if (strlen((string) $value) > 500 || $urlParts === false || !$validScheme || empty($urlParts['host']) || !filter_var($value, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Social links must be valid URLs.');
            }
        }

        $updateParts[] = "{$field} = :{$field}";
        $params[$field] = $value;
    }

    if ($updateParts) {
        $sql = 'UPDATE user_profiles SET ' . implode(', ', $updateParts) . ' WHERE user_id = :user_id';
        $statement = $database->prepare($sql);
        $statement->execute($params);
    }

    $platforms = $_POST['platforms'] ?? [];
    if (isset($_POST['platforms'])) {
        $database->prepare('DELETE FROM user_platforms WHERE user_id = :user_id')->execute(['user_id' => $userId]);
        foreach (array_unique(array_map('strval', $platforms)) as $platform) {
            $platform = trim($platform);
            if ($platform === '') {
                continue;
            }
            $database->prepare('INSERT INTO user_platforms (user_id, platform) VALUES (:user_id, :platform)')->execute([
                'user_id' => $userId,
                'platform' => $platform,
            ]);
        }
    }

    $contentPreferences = $_POST['content_preferences'] ?? [];
    if (isset($_POST['content_preferences'])) {
        $database->prepare('DELETE FROM user_content_preferences WHERE user_id = :user_id')->execute(['user_id' => $userId]);
        foreach (array_unique(array_map('strval', $contentPreferences)) as $contentType) {
            $contentType = trim($contentType);
            if ($contentType === '') {
                continue;
            }
            $database->prepare('INSERT INTO user_content_preferences (user_id, content_type) VALUES (:user_id, :content_type)')->execute([
                'user_id' => $userId,
                'content_type' => $contentType,
            ]);
        }
    }

    $deviceName = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device'));
    $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $loginHistory = $database->prepare(
        'INSERT INTO user_login_history
            (user_id, session_id, device_name, ip_hash, location, user_agent, action, created_at)
         VALUES
            (:user_id, :session_id, :device_name, :ip_hash, :location, :user_agent, :action, NOW())'
    );
    $loginHistory->execute([
        'user_id' => $userId,
        'session_id' => $_SESSION['auth_session_id'],
        'device_name' => $deviceName !== '' ? substr($deviceName, 0, 120) : 'Unknown device',
        'ip_hash' => hash('sha256', $ipAddress),
        'location' => requestLocation(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'action' => 'security_check',
    ]);

    $database->commit();
    header('Location: ' . appUrl('settings') . '?tab=' . urlencode($tab) . '&saved=1');
    exit;
} catch (Throwable $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }
    error_log($exception->getMessage());
    header('Location: ' . appUrl('settings') . '?tab=' . urlencode($tab) . '&error=1');
    exit;
}
