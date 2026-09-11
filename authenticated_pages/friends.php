<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    header('Location: ' . appUrl('login'));
    exit;
}

$sessionStatement = db()->prepare('SELECT user_id FROM sessions WHERE id = :session_id AND user_id = :user_id AND expires_at > NOW() LIMIT 1');
$sessionStatement->execute(['session_id' => $_SESSION['auth_session_id'], 'user_id' => $_SESSION['user_id']]);
if (!$sessionStatement->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['auth_session_id']);
    header('Location: ' . appUrl('login'));
    exit;
}

$userStatement = db()->prepare('SELECT u.username, p.display_name, p.avatar_url FROM users u INNER JOIN user_profiles p ON p.user_id = u.id WHERE u.id = :user_id LIMIT 1');
$userStatement->execute(['user_id' => $_SESSION['user_id']]);
$user = $userStatement->fetch() ?: ['username' => 'gamer', 'display_name' => 'Gamer', 'avatar_url' => null];
$name = (string) $user['display_name'];
$initials = strtoupper(substr($name, 0, 1) . substr((string) $user['username'], 0, 1));
$avatar = $user['avatar_url'] ?: appUrl('assets/icons/profile.png');
$profileUrl = appUrl('@' . $user['username']);
$dashboardActivePage = 'friends';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamersHUB | Friends</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/layout.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/coming_soon.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/friends.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard-page friends-page">
    <div class="dashboard-backdrop" id="dashboardBackdrop"></div>
    <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

    <div class="dashboard-main" id="dashboardMain">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="friends-main" data-api-url="<?= htmlspecialchars(appUrl('api/friends.php'), ENT_QUOTES, 'UTF-8') ?>" data-message-url="<?= htmlspecialchars(appUrl('message'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="friends-heading">
                <div><h1>Friends</h1><p>Your gaming network</p></div>
                <label class="friends-global-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input id="globalFriendSearch" type="search" placeholder="Search friends..." autocomplete="off"></label>
            </div>
            <nav class="friends-tabs" aria-label="Friends views">
                <button class="friends-tab active" type="button" data-tab="friends">All Friends <span data-count="friends">0</span></button>
                <button class="friends-tab" type="button" data-tab="requests">Requests <span data-count="requests">0</span></button>
                <button class="friends-tab" type="button" data-tab="sent">Sent <span data-count="sent">0</span></button>
                <button class="friends-tab" type="button" data-tab="gamers">Find Gamers</button>
            </nav>
            <div class="friends-grid">
                <section class="friends-card"><header><div><h2 id="friendsSectionTitle">Your Friends</h2><p id="friendsSectionDescription">Players in your gaming network</p></div><span id="friendsSectionCount"></span></header><div class="friends-card-body"><div class="friends-toolbar"><label class="friends-list-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input id="friendsSearch" type="search" placeholder="Search your friends..." autocomplete="off"></label></div><div id="friendsList" aria-live="polite"><div class="friends-loading">Loading your network...</div></div></div></section>
            </div>
        </main>
        <?php require __DIR__ . '/../includes/shared_footer.php'; ?>
    </div>
    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/layout_functions.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(appUrl('assets/js/friends.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/friends.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
