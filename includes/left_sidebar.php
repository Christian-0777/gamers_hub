<?php

$dashboardActivePage = $dashboardActivePage ?? 'home';
$name = $name ?? 'Gamer';
$avatar = $avatar ?? appUrl('assets/icons/profile.png');
$profileUrl = $profileUrl ?? appUrl('home');

?>
<aside class="dashboard-sidebar" id="dashboardSidebar">
    <div class="dashboard-brand">
        <img class="dashboard-brand-mark" src="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt="">
        <span class="dashboard-brand-name">GamersHUB</span>
    </div>

    <nav class="dashboard-nav" aria-label="Dashboard navigation">
        <div class="dashboard-section-title">Overview</div>
        <a href="<?= htmlspecialchars(appUrl('home'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item<?= $dashboardActivePage === 'home' ? ' active' : '' ?>">
            <span class="material-symbols-rounded" aria-hidden="true">dynamic_feed</span>
            <span class="dashboard-nav-label">Your Feed</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('analytics'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item<?= $dashboardActivePage === 'analytics' ? ' active' : '' ?>">
            <span class="material-symbols-rounded" aria-hidden="true">analytics</span>
            <span class="dashboard-nav-label">Analytics</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('mygames'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item<?= $dashboardActivePage === 'mygames' ? ' active' : '' ?>">
            <span class="material-symbols-rounded" aria-hidden="true">sports_esports</span>
            <span class="dashboard-nav-label">My games</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('friends'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item<?= $dashboardActivePage === 'friends' ? ' active' : '' ?>">
            <span class="material-symbols-rounded" aria-hidden="true">groups</span>
            <span class="dashboard-nav-label">Friends</span>
        </a>

        <div class="dashboard-section-title">Manage</div>
        <a href="#" class="dashboard-nav-item" data-coming-soon data-feature="Achievements">
            <span class="material-symbols-rounded" aria-hidden="true">trophy</span>
            <span class="dashboard-nav-label">Achievements</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('message'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item<?= $dashboardActivePage === 'message' ? ' active' : '' ?>">
            <span class="material-symbols-rounded" aria-hidden="true">chat</span>
            <span class="dashboard-nav-label">Messages</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('notifications'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item<?= $dashboardActivePage === 'notifications' ? ' active' : '' ?>">
            <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
            <span class="dashboard-nav-label">Notifications</span>
        </a>
        <a href="#" class="dashboard-nav-item" data-coming-soon data-feature="GClan">
            <span class="material-symbols-rounded" aria-hidden="true">groups</span>
            <span class="dashboard-nav-label">GClan</span>
        </a>

        <div class="dashboard-section-title">Support</div>
        <a href="<?= htmlspecialchars(appUrl('partials/wiki.html'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item" target="_blank" rel="noopener noreferrer">
            <span class="material-symbols-rounded" aria-hidden="true">menu_book</span>
            <span class="dashboard-nav-label">Wiki</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('partials/change_log.html'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item" target="_blank" rel="noopener noreferrer">
            <span class="material-symbols-rounded" aria-hidden="true">history</span>
            <span class="dashboard-nav-label">Changelog</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('partials/privacy-policy.html'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item" target="_blank" rel="noopener noreferrer">
            <span class="material-symbols-rounded" aria-hidden="true">privacy_tip</span>
            <span class="dashboard-nav-label">Privacy Policy</span>
        </a>
        <a href="<?= htmlspecialchars(appUrl('partials/terms-of-use.html'), ENT_QUOTES, 'UTF-8') ?>" class="dashboard-nav-item" target="_blank" rel="noopener noreferrer">
            <span class="material-symbols-rounded" aria-hidden="true">gavel</span>
            <span class="dashboard-nav-label">Terms of Use</span>
        </a>
    </nav>

    <div class="dashboard-sidebar-footer">
        <div class="dashboard-sidebar-user">
            <img class="dashboard-avatar" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
            <div class="dashboard-user-copy">
                <div class="dashboard-user-name"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="dashboard-user-role">Gamer</div>
            </div>
        </div>
    </div>
</aside>

<?php require __DIR__ . '/coming_soon_modal.php'; ?>
