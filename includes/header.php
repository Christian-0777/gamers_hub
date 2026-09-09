<?php

$profileUrl = $profileUrl ?? appUrl('home');
$initials = $initials ?? 'G';
$headerAvatar = $avatar ?? appUrl('assets/icons/profile.png');

?>
<header class="dashboard-topbar">
    <button
        class="dashboard-icon-btn"
        id="dashboardSidebarToggle"
        type="button"
        aria-label="Toggle sidebar"
        aria-controls="dashboardSidebar"
    >
        <span class="material-symbols-rounded" aria-hidden="true">menu</span>
    </button>

    <div class="dashboard-search dashboard-desktop-only">
        <div class="input-group">
            <span class="input-group-text bg-transparent border-end-0">
                <span class="material-symbols-rounded text-muted" aria-hidden="true">search</span>
            </span>
            <input
                type="search"
                class="form-control border-start-0 ps-0"
                placeholder="Search games, friends, posts..."
            >
        </div>
    </div>

    <div class="dashboard-topbar-actions ms-auto">
        <span class="dashboard-latency d-none d-sm-flex align-items-center gap-2 text-muted-custom small" title="Connection latency" data-ping-url="<?= htmlspecialchars(appUrl('api/messages.php'), ENT_QUOTES, 'UTF-8') ?>">
            <span class="dashboard-status-dot"></span>
            <span>Ping: <strong id="currentPing">--</strong>ms</span>
        </span>

        <div class="dashboard-notification-menu">
            <button class="dashboard-icon-btn position-relative" id="dashboardNotificationTrigger" type="button" aria-label="Notifications" aria-controls="dashboardNotificationDropdown" aria-expanded="false" data-notification-api-url="<?= htmlspecialchars(appUrl('api/notifications.php'), ENT_QUOTES, 'UTF-8') ?>">
                <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
                <span class="dashboard-notification-badge" id="dashboardNotificationBadge" hidden>0</span>
            </button>
            <div class="dashboard-notification-dropdown" id="dashboardNotificationDropdown" hidden>
                <div class="dashboard-notification-heading"><strong>Notifications</strong><span id="dashboardNotificationCount">0 unread</span></div>
                <div id="dashboardNotificationList"><div class="dashboard-notification-loading">Loading notifications...</div></div>
                <a class="dashboard-notification-more" href="<?= htmlspecialchars(appUrl('notifications'), ENT_QUOTES, 'UTF-8') ?>">View more <span class="material-symbols-rounded" aria-hidden="true">arrow_forward</span></a>
            </div>
        </div>

        <button class="dashboard-icon-btn dashboard-mobile-right-toggle" id="dashboardMobileRightSidebarToggle" type="button" aria-label="Open community sidebar" aria-controls="dashboardRightSidebar" aria-expanded="false">
            <span class="material-symbols-rounded" aria-hidden="true">right_panel_open</span>
        </button>

        <div class="dashboard-account-menu">
            <button
            class="dashboard-avatar dashboard-account-trigger dashboard-header-avatar"
                id="dashboardAccountTrigger"
                type="button"
                aria-label="Open account menu"
                aria-controls="dashboardAccountMenu"
                aria-expanded="false"
            >
                <img src="<?= htmlspecialchars($headerAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
                <span class="visually-hidden"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
            </button>
            <div class="dashboard-account-dropdown" id="dashboardAccountMenu" role="menu" hidden>
                <a href="<?= htmlspecialchars($profileUrl, ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                    <span class="material-symbols-rounded" aria-hidden="true">person</span>
                    View profile
                </a>
                <a href="<?= htmlspecialchars(appUrl('settings'), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                    <span class="material-symbols-rounded" aria-hidden="true">settings</span>
                    Settings
                </a>
                <a href="<?= htmlspecialchars(appUrl('logout'), ENT_QUOTES, 'UTF-8') ?>" role="menuitem">
                    <span class="material-symbols-rounded" aria-hidden="true">logout</span>
                    Log out
                </a>
            </div>
        </div>
    </div>
</header>

<?php require __DIR__ . '/right_sidebar.php'; ?>
