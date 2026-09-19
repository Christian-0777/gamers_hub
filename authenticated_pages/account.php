<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    header('Location: ' . appUrl('login'));
    exit;
}

$sessionStatement = db()->prepare(
    'SELECT user_id
     FROM sessions
     WHERE id = :id
       AND user_id = :user_id
       AND expires_at > NOW()
     LIMIT 1'
);

$sessionStatement->execute([
    'id'      => $_SESSION['auth_session_id'],
    'user_id' => $_SESSION['user_id'],
]);

if (!$sessionStatement->fetch()) {
    unset(
        $_SESSION['user_id'],
        $_SESSION['auth_session_id']
    );

    header('Location: ' . appUrl('login'));
    exit;
}

$userStatement = db()->prepare(
    'SELECT
        username,
        display_name,
        bio,
        social_discord,
        social_tiktok,
        social_facebook,
        social_twitch,
        social_kick,
        social_github,
        social_spotify,
        social_youtube,
        social_apple_music,
        social_steam,
        social_x,
        avatar_url,
        cover_url,
        gaming_style,
        preferred_voice_chat,
        profile_visibility,
        who_can_message,
        email_notifications,
        push_notifications,
        social_notifications,
        two_factor_enabled
     FROM users u
     INNER JOIN user_profiles p
        ON p.user_id = u.id
     WHERE u.id = :user_id
     LIMIT 1'
);

$userStatement->execute([
    'user_id' => $_SESSION['user_id'],
]);

$user = $userStatement->fetch() ?: [
    'username'     => 'gamer',
    'display_name' => 'Gamer',
    'bio'          => null,
    'social_discord' => null,
    'social_tiktok' => null,
    'social_facebook' => null,
    'social_twitch' => null,
    'social_kick' => null,
    'social_github' => null,
    'social_spotify' => null,
    'social_youtube' => null,
    'social_apple_music' => null,
    'social_steam' => null,
    'social_x' => null,
    'avatar_url'   => null,
    'cover_url'    => null,
    'gaming_style' => 'both',
    'preferred_voice_chat' => true,
    'profile_visibility' => 'public',
    'who_can_message' => 'everyone',
    'email_notifications' => true,
    'push_notifications' => true,
    'social_notifications' => true,
    'two_factor_enabled' => false,
];

$userId = (int) $_SESSION['user_id'];
$defaultAvatar = appUrl('assets/icons/profile.png');
$defaultCover = $user['cover_url'] ?: $defaultAvatar;

$platformStatement = db()->prepare(
    'SELECT platform FROM user_platforms WHERE user_id = :user_id ORDER BY platform'
);
$platformStatement->execute(['user_id' => $userId]);
$selectedPlatforms = $platformStatement->fetchAll(PDO::FETCH_COLUMN);

$contentStatement = db()->prepare(
    'SELECT content_type FROM user_content_preferences WHERE user_id = :user_id ORDER BY content_type'
);
$contentStatement->execute(['user_id' => $userId]);
$selectedContent = $contentStatement->fetchAll(PDO::FETCH_COLUMN);

$gamePreferenceStatement = db()->prepare(
    'SELECT g.id, g.name,
            (SELECT cc.name
             FROM game_companies gc
             INNER JOIN company_catalog cc ON cc.id = gc.company_id
             WHERE gc.game_id = g.id AND gc.role = \'developer\'
             ORDER BY cc.name ASC LIMIT 1) AS developer
     FROM user_games ug
    INNER JOIN game_catalog g ON g.id = ug.game_id
     WHERE ug.user_id = :user_id
     ORDER BY g.name'
);
$gamePreferenceStatement->execute(['user_id' => $userId]);
$selectedGames = $gamePreferenceStatement->fetchAll();

$developerPreferenceStatement = db()->prepare(
    'SELECT cc.name
     FROM user_companies uc
     INNER JOIN company_catalog cc ON cc.id = uc.company_id
     WHERE uc.user_id = :user_id AND uc.role = \'developer\'
     ORDER BY cc.name'
);
$developerPreferenceStatement->execute(['user_id' => $userId]);
$selectedDevelopers = $developerPreferenceStatement->fetchAll(PDO::FETCH_COLUMN);

$sessionRows = db()->prepare(
    'SELECT id, ip_address, user_agent, created_at, expires_at
     FROM sessions
     WHERE user_id = :user_id
     ORDER BY created_at DESC'
);
$sessionRows->execute(['user_id' => $userId]);
$activeSessions = $sessionRows->fetchAll();

$historyRows = db()->prepare(
    'SELECT
        h.id,
        h.session_id,
        h.device_name,
        h.location,
        h.action,
        h.created_at,
        s.id AS active_session_id,
        s.ip_address
     FROM user_login_history h
     LEFT JOIN sessions s ON s.id = h.session_id AND s.user_id = h.user_id
     WHERE h.user_id = :user_id
     ORDER BY h.created_at DESC
     LIMIT 20'
);
$historyRows->execute(['user_id' => $userId]);
$loginEntries = $historyRows->fetchAll();

$availablePlatforms = ['PC', 'PlayStation', 'Xbox', 'Nintendo Switch', 'Mobile', 'Steam Deck', 'Other'];
$availableContent = [
    'discussions' => 'Game discussions',
    'tips' => 'Gaming tips',
    'achievements' => 'Achievements',
    'lfg' => 'LFG posts',
    'reviews' => 'Game reviews',
    'news' => 'Gaming news',
    'memes' => 'Memes / casual posts',
];

$profileVisibilityOptions = [
    'public' => 'Public - everyone can see',
    'friends_only' => 'Friends only',
    'followers_only' => 'Followers only',
    'private' => 'Private - no one can see',
];

$messageVisibilityOptions = [
    'everyone' => 'Everyone',
    'followers' => 'Followers',
    'friends' => 'Friends only',
];

$tab = (string) ($_GET['tab'] ?? 'account');
if (!in_array($tab, ['account', 'gamer-preference', 'privacy', 'notifications', 'security', 'social'], true)) {
    $tab = 'account';
}

$errorMessage = isset($_GET['error']) && $_GET['error'] === '1'
    ? 'We could not update your settings. Please check the uploaded images and try again.'
    : null;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$name = (string) $user['display_name'];

$initials = strtoupper(
    substr($name, 0, 1) .
    substr((string) $user['username'], 0, 1)
);

$avatar = $user['avatar_url']
    ?: appUrl('assets/icons/profile.png');

$profileUrl = appUrl('@' . $user['username']);
$dashboardLayout = true;
$dashboardActivePage = 'settings';

?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>GamersHUB | Dashboard</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    >

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            appUrl('assets/css/shared/layout.css?v=2'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            appUrl('assets/css/account.css'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/coming_soon.css'), ENT_QUOTES, 'UTF-8') ?>">

</head>

<body class="dashboard-page">

    <div
        class="dashboard-backdrop"
        id="dashboardBackdrop"
    ></div>

    <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

    <div
        class="dashboard-main"
        id="dashboardMain"
    >

        <?php require __DIR__ . '/../includes/header.php'; ?>

        <main class="page-content settings-page">
        <div class="settings-topbar">
            <div>
                <p class="eyebrow">ACCOUNT</p>
                <h1>Settings</h1>
            </div>
        </div>

        <nav class="settings-tabs" aria-label="Settings tabs">
            <a href="<?= htmlspecialchars(appUrl('settings') . '?tab=account', ENT_QUOTES, 'UTF-8') ?>" class="settings-tab <?= $tab === 'account' ? 'active' : '' ?>" data-settings-tab="account">Account</a>
            <a href="<?= htmlspecialchars(appUrl('settings') . '?tab=gamer-preference', ENT_QUOTES, 'UTF-8') ?>" class="settings-tab <?= $tab === 'gamer-preference' ? 'active' : '' ?>" data-settings-tab="gamer-preference">Gamer's Preference</a>
            <a href="<?= htmlspecialchars(appUrl('settings') . '?tab=privacy', ENT_QUOTES, 'UTF-8') ?>" class="settings-tab <?= $tab === 'privacy' ? 'active' : '' ?>" data-settings-tab="privacy">Privacy</a>
            <a href="<?= htmlspecialchars(appUrl('settings') . '?tab=notifications', ENT_QUOTES, 'UTF-8') ?>" class="settings-tab <?= $tab === 'notifications' ? 'active' : '' ?>" data-settings-tab="notifications">Notifications</a>
            <a href="<?= htmlspecialchars(appUrl('settings') . '?tab=security', ENT_QUOTES, 'UTF-8') ?>" class="settings-tab <?= $tab === 'security' ? 'active' : '' ?>" data-settings-tab="security">Security</a>
            <a href="<?= htmlspecialchars(appUrl('settings') . '?tab=social', ENT_QUOTES, 'UTF-8') ?>" class="settings-tab <?= $tab === 'social' ? 'active' : '' ?>" data-settings-tab="social">Social</a>
        </nav>

        <?php if ($errorMessage): ?>
            <div class="form-alert" role="alert">
                <p><?= htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        <?php endif; ?>

        <form class="settings-form" method="post" action="<?= htmlspecialchars(appUrl('api/update_profile.php'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="tab" value="<?= htmlspecialchars($tab, ENT_QUOTES, 'UTF-8') ?>">
            <?php foreach ($selectedGames as $game): ?>
                <input type="hidden" name="games[]" value="<?= (int) $game['id'] ?>" data-account-game-input="<?= (int) $game['id'] ?>">
            <?php endforeach; ?>
            <?php foreach ($selectedDevelopers as $developer): ?>
                <input type="hidden" name="developers[]" value="<?= htmlspecialchars((string) $developer, ENT_QUOTES, 'UTF-8') ?>" data-account-developer-input="<?= htmlspecialchars((string) $developer, ENT_QUOTES, 'UTF-8') ?>">
            <?php endforeach; ?>

            <section class="settings-panel <?= $tab === 'account' ? 'active' : '' ?>" data-settings-panel="account">
                <div class="settings-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">PROFILE</p>
                            <h2>Gamer details</h2>
                        </div>
                    </div>

                    <div class="field-grid two-up">
                        <div class="field">
                            <span>Profile photo</span>
                            <div class="image-preview avatar-preview">
                                <img src="<?= htmlspecialchars((string) (($user['avatar_url'] ?? '') ?: $defaultAvatar), ENT_QUOTES, 'UTF-8') ?>" alt="Profile preview" data-avatar-preview>
                            </div>
                            <label class="upload-box">
                                <input type="file" name="avatar_file" accept="image/*" data-avatar-upload>
                                <span>Upload profile photo</span>
                            </label>
                        </div>
                        <div class="field">
                            <span>Cover photo</span>
                            <div class="image-preview cover-preview">
                                <img src="<?= htmlspecialchars((string) (($user['cover_url'] ?? '') ?: $defaultCover), ENT_QUOTES, 'UTF-8') ?>" alt="Cover preview" data-cover-preview>
                            </div>
                            <label class="upload-box">
                                <input type="file" name="cover_file" accept="image/*" data-cover-upload>
                                <span>Upload cover photo</span>
                            </label>
                        </div>
                        <label class="field full-width">
                            <span>Display name</span>
                            <input type="text" name="display_name" maxlength="50" value="<?= htmlspecialchars((string) ($user['display_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Leave blank to keep current name">
                        </label>
                        <label class="field full-width">
                            <span>Bio</span>
                            <textarea name="bio" rows="5" maxlength="500" placeholder="Leave blank to keep current bio"><?= htmlspecialchars((string) ($user['bio'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        </label>
                    </div>
                </div>

            </section>

            <section class="settings-panel <?= $tab === 'gamer-preference' ? 'active' : '' ?>" data-settings-panel="gamer-preference">
                <div class="settings-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">GAMER PROFILE</p>
                            <h2>Gamer's Preference</h2>
                        </div>
                    </div>

                    <div class="field-grid two-up">
                        <label class="field">
                            <span>Gaming style</span>
                            <select name="gaming_style">
                                <option value="casual" <?= (($user['gaming_style'] ?? '') === 'casual') ? 'selected' : '' ?>>Casual</option>
                                <option value="competitive" <?= (($user['gaming_style'] ?? '') === 'competitive') ? 'selected' : '' ?>>Competitive</option>
                                <option value="both" <?= (($user['gaming_style'] ?? '') === 'both') ? 'selected' : '' ?>>Both</option>
                            </select>
                        </label>

                        <div class="field">
                            <span>Voice chat availability</span>
                            <label class="toggle-row">
                                <input type="checkbox" name="preferred_voice_chat" value="1" <?= !empty($user['preferred_voice_chat']) ? 'checked' : '' ?>>
                                <span>Open to voice chat</span>
                            </label>
                        </div>
                    </div>

                    <div class="field-group">
                        <span class="group-label">Game platforms</span>
                        <div class="choice-grid">
                            <?php foreach ($availablePlatforms as $platform): ?>
                                <label class="choice-item">
                                    <input type="checkbox" name="platforms[]" value="<?= htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($platform, $selectedPlatforms, true) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="field-group">
                        <span class="group-label">Content preference</span>
                        <div class="choice-grid">
                            <?php foreach ($availableContent as $key => $label): ?>
                                <label class="choice-item">
                                    <input type="checkbox" name="content_preferences[]" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>" <?= in_array($key, $selectedContent, true) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="settings-card preference-catalog-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">LIBRARY</p>
                            <h2>Games you play</h2>
                        </div>
                    </div>
                    <label class="field preference-search">
                        <span>Search games by name or developer</span>
                        <input type="search" id="account-game-search" placeholder="Start typing to search..." autocomplete="off">
                    </label>
                    <div class="preference-catalog-table-wrap">
                        <table class="preference-catalog-table">
                            <thead><tr><th>Game</th><th>Developer</th><th>Action</th></tr></thead>
                            <tbody id="account-game-results"></tbody>
                        </table>
                    </div>
                    <h3 class="preference-list-title">Added games</h3>
                    <div class="preference-catalog-table-wrap">
                        <table class="preference-catalog-table">
                            <tbody id="account-selected-games"></tbody>
                        </table>
                    </div>
                </div>

                <div class="settings-card preference-catalog-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">DISCOVERY</p>
                            <h2>Developers you like</h2>
                        </div>
                    </div>
                    <label class="field preference-search">
                        <span>Search developers</span>
                        <input type="search" id="account-developer-search" placeholder="Start typing to search..." autocomplete="off">
                    </label>
                    <div class="preference-catalog-table-wrap">
                        <table class="preference-catalog-table">
                            <thead><tr><th>Developer</th><th>Action</th></tr></thead>
                            <tbody id="account-developer-results"></tbody>
                        </table>
                    </div>
                    <h3 class="preference-list-title">Added developers</h3>
                    <div class="preference-catalog-table-wrap">
                        <table class="preference-catalog-table">
                            <tbody id="account-selected-developers"></tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="settings-panel <?= $tab === 'privacy' ? 'active' : '' ?>" data-settings-panel="privacy">
                <div class="settings-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">VISIBILITY</p>
                            <h2>Privacy</h2>
                        </div>
                    </div>

                    <div class="field-group">
                        <span class="group-label">Profile visibility</span>
                        <div class="radio-list">
                            <?php foreach ($profileVisibilityOptions as $value => $label): ?>
                                <label class="radio-item">
                                    <input type="radio" name="profile_visibility" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= (($user['profile_visibility'] ?? 'public') === $value) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="field-group">
                        <span class="group-label">Who can message me</span>
                        <div class="radio-list">
                            <?php foreach ($messageVisibilityOptions as $value => $label): ?>
                                <label class="radio-item">
                                    <input type="radio" name="who_can_message" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>" <?= (($user['who_can_message'] ?? 'everyone') === $value) ? 'checked' : '' ?>>
                                    <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>

            <section class="settings-panel <?= $tab === 'notifications' ? 'active' : '' ?>" data-settings-panel="notifications">
                <div class="settings-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">PUSH</p>
                            <h2>Notification preferences</h2>
                        </div>
                    </div>

                    <div class="toggle-stack">
                        <label class="toggle-row checkbox-row">
                            <input type="checkbox" name="email_notifications" value="1" <?= !empty($user['email_notifications']) ? 'checked' : '' ?>>
                            <span>Email notifications</span>
                        </label>
                        <label class="toggle-row checkbox-row">
                            <input type="checkbox" name="push_notifications" value="1" <?= !empty($user['push_notifications']) ? 'checked' : '' ?>>
                            <span>Push notifications</span>
                        </label>
                        <label class="toggle-row checkbox-row">
                            <input type="checkbox" name="social_notifications" value="1" <?= !empty($user['social_notifications']) ? 'checked' : '' ?>>
                            <span>Social notifications</span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="settings-panel <?= $tab === 'security' ? 'active' : '' ?>" data-settings-panel="security">
                <div class="settings-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">SAFE ACCESS</p>
                            <h2>Security</h2>
                        </div>
                    </div>

                    <div class="security-wrap">
                        <div class="security-box">
                            <div class="security-title">Two-factor authentication</div>
                            <label class="toggle-row">
                                <input type="checkbox" name="two_factor_enabled" value="1" <?= !empty($user['two_factor_enabled']) ? 'checked' : '' ?>>
                                <span>Enable 2FA</span>
                            </label>
                        </div>

                        <div class="security-box">
                            <div class="security-title">Active sessions</div>
                            <div class="session-list">
                                <?php foreach ($activeSessions as $session): ?>
                                    <?php
                                    $isCurrentSession = ($session['id'] ?? '') === ($_SESSION['auth_session_id'] ?? '');
                                    $deviceName = $session['user_agent'] ?? 'Unknown device';
                                    $ipAddress = $session['ip_address'] ?? 'Unavailable';
                                    ?>
                                    <div class="session-item <?= $isCurrentSession ? 'current' : '' ?>">
                                        <div>
                                            <strong><?= htmlspecialchars($isCurrentSession ? 'Current device' : $deviceName, ENT_QUOTES, 'UTF-8') ?></strong>
                                            <small><?= htmlspecialchars($isCurrentSession ? 'This browser session' : trim(substr($deviceName, 0, 80)), ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                        <div class="session-meta">
                                            <span><?= htmlspecialchars($ipAddress, ENT_QUOTES, 'UTF-8') ?></span>
                                            <time><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime((string) $session['created_at'])), ENT_QUOTES, 'UTF-8') ?></time>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table class="history-table">
                            <thead>
                            <tr>
                                <th>Device name</th>
                                <th>IP address</th>
                                <th>Location</th>
                                <th>Timestamp</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($loginEntries)): ?>
                                <tr>
                                    <td colspan="5" class="empty-row">No login activity yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($loginEntries as $entry): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($entry['device_name'] ?? 'Unknown device'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($entry['ip_address'] ?? 'Unavailable'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars((string) ($entry['location'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime((string) ($entry['created_at'] ?? 'now'))), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td>
                                            <?php if (!empty($entry['active_session_id'])): ?>
                                                <form method="post" action="<?= htmlspecialchars(appUrl('api/logout_device.php'), ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <input type="hidden" name="session_id" value="<?= htmlspecialchars((string) $entry['active_session_id'], ENT_QUOTES, 'UTF-8') ?>">
                                                    <button class="logout-device-button" type="submit">Log out this device</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="settings-panel <?= $tab === 'social' ? 'active' : '' ?>" data-settings-panel="social">
                <div class="settings-card">
                    <div class="card-heading">
                        <div>
                            <p class="eyebrow">SOCIAL LINKS</p>
                            <h2>Where to find you</h2>
                        </div>
                    </div>

                    <div class="field-grid two-up">
                        <?php foreach ([
                            'discord' => 'Discord',
                            'tiktok' => 'TikTok',
                            'facebook' => 'Facebook',
                            'twitch' => 'Twitch',
                            'kick' => 'Kick',
                            'github' => 'GitHub',
                            'spotify' => 'Spotify',
                            'youtube' => 'YouTube',
                            'apple_music' => 'Apple Music',
                            'steam' => 'Steam',
                            'x' => 'X',
                        ] as $socialKey => $socialLabel): ?>
                            <label class="field">
                                <span><?= htmlspecialchars($socialLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <input type="text" inputmode="url" name="social_<?= htmlspecialchars($socialKey, ENT_QUOTES, 'UTF-8') ?>" maxlength="500" value="<?= htmlspecialchars((string) ($user['social_' . $socialKey] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="tiktok.com/@username">
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <div class="settings-actions">
                <button class="secondary-button" type="button" onclick="window.location.href='<?= htmlspecialchars(appUrl('@' . ($user['username'] ?? '')), ENT_QUOTES, 'UTF-8') ?>'">View profile</button>
                <button class="primary-button" type="submit">Save changes</button>
            </div>
        </form>
    </main>

        <?php require __DIR__ . '/../includes/shared_footer.php'; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <script src="<?= htmlspecialchars(
        appUrl('assets/js/shared/layout_functions.js'),
        ENT_QUOTES,
        'UTF-8'
    ) ?>"></script>

    <script>
        window.accountPreferenceCatalog = <?= json_encode([
            'url' => appUrl('api/list.php'),
            'games' => array_column($selectedGames, null, 'id'),
            'developers' => array_values($selectedDevelopers),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    </script>

    <script
        src="<?= htmlspecialchars(appUrl('assets/js/account.js'), ENT_QUOTES, 'UTF-8') ?>"
        defer
    ></script>

    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

</body>

</html>