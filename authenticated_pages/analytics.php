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
        avatar_url
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
    'avatar_url'   => null,
];

$name = (string) $user['display_name'];

$initials = strtoupper(
    substr($name, 0, 1) .
    substr((string) $user['username'], 0, 1)
);

$avatar = $user['avatar_url']
    ?: appUrl('assets/icons/profile.png');

$profileUrl = appUrl('@' . $user['username']);
$dashboardLayout = true;
$dashboardActivePage = 'analytics';

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
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0"
    >

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            appUrl('assets/css/shared/layout.css'),
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

        <main class="dashboard-content">

            <div
                class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4"
            >

                <div>

                    <h1 class="h4 mb-1">
                        Welcome back,
                        <?= htmlspecialchars(
                            $name,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?>
                    </h1>

                    <p class="text-muted-custom mb-0 small">
                        Here is what is happening in your gaming circle today.
                    </p>

                </div>

                <a
                    class="btn btn-sm d-flex align-items-center gap-2"
                    href="#your-games"
                    style="background:var(--dashboard-accent);color:#fff"
                >
                    <span class="material-symbols-rounded" aria-hidden="true">add</span>
                    Create post
                </a>

            </div>

            <div class="row g-3 mb-4">

                <div class="col-6 col-lg-3">

                    <div class="dashboard-card p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div
                                class="dashboard-stat-icon"
                                style="background:var(--dashboard-accent-soft);color:var(--dashboard-accent)"
                            >
                                <span class="material-symbols-rounded" aria-hidden="true">groups</span>
                            </div>

                            <span class="dashboard-trend dashboard-trend-up">
                                <span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span>
                                12.4%
                            </span>

                        </div>

                        <div class="dashboard-stat-value">
                            248
                        </div>

                        <div class="text-muted-custom small mt-1">
                            Friends in your circle
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="dashboard-card p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div
                                class="dashboard-stat-icon"
                                style="background:var(--dashboard-accent-2-soft);color:var(--dashboard-text)"
                            >
                                <span class="material-symbols-rounded" aria-hidden="true">sports_esports</span>
                            </div>

                            <span class="dashboard-trend dashboard-trend-up">
                                <span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span>
                                4.1%
                            </span>

                        </div>

                        <div class="dashboard-stat-value">
                            8
                        </div>

                        <div class="text-muted-custom small mt-1">
                            Games you play
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="dashboard-card p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div
                                class="dashboard-stat-icon"
                                style="background:var(--dashboard-accent-soft);color:var(--dashboard-accent)"
                            >
                                <span class="material-symbols-rounded" aria-hidden="true">trophy</span>
                            </div>

                            <span class="dashboard-trend dashboard-trend-up">
                                <span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span>
                                2.3%
                            </span>

                        </div>

                        <div class="dashboard-stat-value">
                            1,204
                        </div>

                        <div class="text-muted-custom small mt-1">
                            Achievements unlocked
                        </div>

                    </div>

                </div>

                <div class="col-6 col-lg-3">

                    <div class="dashboard-card p-3 h-100">

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div
                                class="dashboard-stat-icon"
                                style="background:var(--dashboard-danger-soft);color:var(--dashboard-danger)"
                            >
                                <span class="material-symbols-rounded" aria-hidden="true">monitoring</span>
                            </div>

                            <span class="dashboard-trend dashboard-trend-up">
                                <span class="material-symbols-rounded" aria-hidden="true">arrow_upward</span>
                                0.8%
                            </span>

                        </div>

                        <div class="dashboard-stat-value">
                            3.62%
                        </div>

                        <div class="text-muted-custom small mt-1">
                            Weekly activity
                        </div>

                    </div>

                </div>

            </div>

            <div class="row g-3 mb-4">

                <div class="col-lg-8">

                    <div class="dashboard-card p-3 p-md-4 h-100">

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h2 class="h6 mb-0">
                                    Activity over time
                                </h2>

                                <p class="text-muted-custom small mb-0">
                                    Last 6 months
                                </p>

                            </div>

                            <select
                                class="form-select form-select-sm"
                                style="width:auto"
                            >
                                <option>6 months</option>
                                <option>12 months</option>
                                <option>This year</option>
                            </select>

                        </div>

                        <canvas
                            id="dashboardRevenueChart"
                            height="130"
                        ></canvas>

                    </div>

                </div>

                <div class="col-lg-4">

                    <div class="dashboard-card p-3 p-md-4 h-100">

                        <h2 class="h6 mb-3">
                            Recent activity
                        </h2>

                        <div class="dashboard-activity-item">

                            <span
                                class="dashboard-activity-dot"
                                style="background:var(--dashboard-success)"
                            ></span>

                            <div>

                                <div class="small fw-semibold">
                                    Maya reached Ascendant
                                </div>

                                <div
                                    class="text-muted-custom"
                                    style="font-size:.75rem"
                                >
                                    2 minutes ago
                                </div>

                            </div>

                        </div>

                        <div class="dashboard-activity-item">

                            <span
                                class="dashboard-activity-dot"
                                style="background:var(--dashboard-accent-2)"
                            ></span>

                            <div>

                                <div class="small fw-semibold">
                                    New friend request received
                                </div>

                                <div
                                    class="text-muted-custom"
                                    style="font-size:.75rem"
                                >
                                    18 minutes ago
                                </div>

                            </div>

                        </div>

                        <div class="dashboard-activity-item">

                            <span
                                class="dashboard-activity-dot"
                                style="background:var(--dashboard-accent)"
                            ></span>

                            <div>

                                <div class="small fw-semibold">
                                    You joined a Minecraft server
                                </div>

                                <div
                                    class="text-muted-custom"
                                    style="font-size:.75rem"
                                >
                                    1 hour ago
                                </div>

                            </div>

                        </div>

                        <div class="dashboard-activity-item">

                            <span
                                class="dashboard-activity-dot"
                                style="background:var(--dashboard-danger)"
                            ></span>

                            <div>

                                <div class="small fw-semibold">
                                    Weekly challenge completed
                                </div>

                                <div
                                    class="text-muted-custom"
                                    style="font-size:.75rem"
                                >
                                    3 hours ago
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

            <div
                class="dashboard-card p-3 p-md-4"
                id="your-games"
            >

                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h2 class="h6 mb-0">
                        Your games
                    </h2>

                    <a
                        href="#"
                        class="small fw-semibold text-decoration-none"
                        style="color:var(--dashboard-accent)"
                    >
                        View all
                    </a>

                </div>

                <div class="table-responsive">

                    <table class="table dashboard-table mb-0">

                        <thead>

                            <tr>
                                <th>Game</th>
                                <th>Last played</th>
                                <th>Hours this week</th>
                                <th>Status</th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr>
                                <td class="fw-semibold">
                                    VALORANT
                                </td>

                                <td class="text-muted-custom">
                                    Today
                                </td>

                                <td>
                                    12.5 hrs
                                </td>

                                <td>
                                    <span class="dashboard-badge dashboard-badge-paid">
                                        Active
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-semibold">
                                    Minecraft
                                </td>

                                <td class="text-muted-custom">
                                    Yesterday
                                </td>

                                <td>
                                    8.2 hrs
                                </td>

                                <td>
                                    <span class="dashboard-badge dashboard-badge-paid">
                                        Active
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-semibold">
                                    Apex Legends
                                </td>

                                <td class="text-muted-custom">
                                    Aug 19, 2026
                                </td>

                                <td>
                                    4.7 hrs
                                </td>

                                <td>
                                    <span class="dashboard-badge dashboard-badge-pending">
                                        Played recently
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="fw-semibold">
                                    League of Legends
                                </td>

                                <td class="text-muted-custom">
                                    Aug 18, 2026
                                </td>

                                <td>
                                    2.1 hrs
                                </td>

                                <td>
                                    <span class="dashboard-badge dashboard-badge-failed">
                                        Needs attention
                                    </span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

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

    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

</body>

</html>