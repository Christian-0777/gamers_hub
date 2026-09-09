<?php

require_once __DIR__ . '/../config/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$rightSidebarUsers = [];
$currentUserId = (int) ($_SESSION['user_id'] ?? 0);

if ($currentUserId > 0) {
    $friendStatement = db()->prepare(
        'SELECT u.id, u.username, p.display_name, p.online_status,
                GROUP_CONCAT(DISTINCT g.name ORDER BY ug.updated_at DESC SEPARATOR ", ") AS games,
                COALESCE(a.weekly_activity, 0) AS weekly_activity
         FROM followers outgoing
         INNER JOIN followers incoming
             ON incoming.follower_id = outgoing.following_id
            AND incoming.following_id = outgoing.follower_id
         INNER JOIN users u ON u.id = outgoing.following_id AND u.status = "active"
         INNER JOIN user_profiles p ON p.user_id = u.id
         LEFT JOIN user_games ug
             ON ug.user_id = u.id
            AND ug.status IN ("playing", "favorite")
         LEFT JOIN games g ON g.id = ug.game_id
         LEFT JOIN (
             SELECT user_id, COUNT(*) AS weekly_activity
             FROM posts
             WHERE status = "published"
               AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY user_id
         ) a ON a.user_id = u.id
         WHERE outgoing.follower_id = :user_id
         GROUP BY u.id, u.username, p.display_name, p.online_status, a.weekly_activity
         ORDER BY p.online_status = "online" DESC, p.display_name ASC'
    );
    $friendStatement->execute(['user_id' => $currentUserId]);

    $avatarColors = ['#6C55D9', '#2E5AAC', '#F4691F', '#2F9E5B', '#C04B82'];
    foreach ($friendStatement->fetchAll() as $index => $friend) {
        $name = (string) ($friend['display_name'] ?: $friend['username']);
        $rightSidebarUsers[] = [
            'name' => $name,
            'game' => (string) (($friend['games'] ? explode(', ', $friend['games'])[0] : '') ?: 'No game selected'),
            'activity' => (int) $friend['weekly_activity'] . ' activities this week',
            'initials' => strtoupper(substr($name, 0, 1) . substr((string) $friend['username'], 0, 1)),
            'color' => $avatarColors[$index % count($avatarColors)],
            'status' => $friend['online_status'] ?: 'offline',
        ];
    }
}

$rightSidebarOnlineCount = count(array_filter(
    $rightSidebarUsers,
    static fn(array $user): bool => $user['status'] === 'online'
));
$rightSidebarTotalCount = count($rightSidebarUsers);

?>
<aside class="dashboard-right-sidebar" id="dashboardRightSidebar" aria-label="Community sidebar">
    <div class="dashboard-right-sidebar-inner">
        <div class="dashboard-right-sidebar-toolbar">
            <button class="dashboard-icon-btn" id="dashboardRightSidebarToggle" type="button" aria-label="Collapse community sidebar" aria-controls="dashboardRightSidebar" aria-expanded="true">
                <span class="material-symbols-rounded" aria-hidden="true">right_panel_close</span>
            </button>
            <strong class="dashboard-right-sidebar-title">Community</strong>
        </div>

        <section class="dashboard-right-sidebar-section">
            <div class="dashboard-right-sidebar-heading">
                <span class="dashboard-right-sidebar-label">Friends activity</span>
                <span class="dashboard-right-sidebar-count"><?= $rightSidebarOnlineCount ?>/<?= $rightSidebarTotalCount ?> online</span>
            </div>

            <div class="dashboard-right-sidebar-list">
                <?php foreach ($rightSidebarUsers as $user): ?>
                    <a class="dashboard-right-sidebar-user" href="<?= htmlspecialchars(appUrl('friends'), ENT_QUOTES, 'UTF-8') ?>">
                        <span class="dashboard-right-sidebar-avatar-wrap">
                            <span class="dashboard-right-sidebar-avatar" style="background:<?= htmlspecialchars($user['color'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($user['initials'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="dashboard-right-sidebar-status is-<?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8') ?>" aria-label="<?= htmlspecialchars(ucfirst($user['status']), ENT_QUOTES, 'UTF-8') ?>"></span>
                        </span>
                        <span class="dashboard-right-sidebar-user-copy">
                            <span class="dashboard-right-sidebar-user-name"><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <span class="dashboard-right-sidebar-user-meta"><?= htmlspecialchars($user['game'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($user['activity'], ENT_QUOTES, 'UTF-8') ?></span>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="dashboard-right-sidebar-section">
            <div class="dashboard-right-sidebar-heading">
                <span class="dashboard-right-sidebar-label">GClan members online</span>
                <span class="dashboard-right-sidebar-coming-soon">Coming soon</span>
            </div>

            <div class="dashboard-right-sidebar-coming-soon-panel">
                <span class="material-symbols-rounded" aria-hidden="true">groups</span>
                <strong>GClan online list</strong>
                <span>See your clan members and their current games here soon.</span>
            </div>
        </section>
    </div>
</aside>
