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

        <button
            class="dashboard-icon-btn position-relative"
            type="button"
            aria-label="Notifications"
        >
            <span class="material-symbols-rounded" aria-hidden="true">notifications</span>
            <span
                class="position-absolute top-0 start-100 translate-middle badge rounded-pill"
                style="background:var(--dashboard-danger);width:9px;height:9px;padding:0"
            ></span>
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
