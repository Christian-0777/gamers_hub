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
    'SELECT user_id FROM sessions WHERE id = :session_id AND user_id = :user_id AND expires_at > NOW() LIMIT 1'
);
$sessionStatement->execute([
    'session_id' => $_SESSION['auth_session_id'],
    'user_id' => $_SESSION['user_id'],
]);

if (!$sessionStatement->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['auth_session_id']);
    header('Location: ' . appUrl('login'));
    exit;
}

$userStatement = db()->prepare(
    'SELECT u.username, p.display_name, p.avatar_url FROM users u
     INNER JOIN user_profiles p ON p.user_id = u.id WHERE u.id = :user_id LIMIT 1'
);
$userStatement->execute(['user_id' => $_SESSION['user_id']]);
$user = $userStatement->fetch() ?: ['username' => 'gamer', 'display_name' => 'Gamer', 'avatar_url' => null];
$name = (string) $user['display_name'];
$initials = strtoupper(substr($name, 0, 1) . substr((string) $user['username'], 0, 1));
$avatar = $user['avatar_url'] ?: appUrl('assets/icons/profile.png');
$profileUrl = appUrl('@' . $user['username']);
$dashboardActivePage = 'notifications';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamersHUB | Notifications</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/layout.css?v=2'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/coming_soon.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/notifications.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard-page notifications-page">
    <div class="dashboard-backdrop" id="dashboardBackdrop"></div>
    <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

    <div class="dashboard-main" id="dashboardMain">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="notification-main" data-api-url="<?= htmlspecialchars(appUrl('api/notifications.php'), ENT_QUOTES, 'UTF-8') ?>" data-page-url="<?= htmlspecialchars(appUrl('notifications'), ENT_QUOTES, 'UTF-8') ?>">
            <div class="notification-heading">
                <div>
                    <div class="notification-title-row"><h1>Notifications</h1><span class="notification-count" id="notificationCount" hidden>0</span></div>
                    <p>Stay updated with your gaming activity.</p>
                </div>
                <div class="notification-actions">
                    <button class="notification-filter" id="notificationFilter" type="button" aria-pressed="false"><span class="material-symbols-rounded" aria-hidden="true">filter_alt</span><span>All</span></button>
                    <button class="notification-mark-all" id="notificationMarkAll" type="button"><span class="material-symbols-rounded" aria-hidden="true">done_all</span>Mark all as read</button>
                </div>
            </div>
            <section class="notification-card" id="notificationList" aria-live="polite"><div class="notification-loading">Loading notifications...</div></section>
        </main>
        <?php require __DIR__ . '/../includes/shared_footer.php'; ?>
    </div>
    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/layout_functions.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(appUrl('assets/js/notifications.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
