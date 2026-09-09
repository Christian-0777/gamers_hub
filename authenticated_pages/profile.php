<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';
require_once __DIR__ . '/../api/profile_fetch.php';

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
        u.id,
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
        online_status
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
    'id'           => $_SESSION['user_id'],
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
    'gaming_style' => null,
    'online_status'=> null,
];

$userId = (int) $user['id'];
$username = (string) $user['username'];
$defaultAvatar = appUrl('assets/icons/profile.png');
$stats = getUserStats(db(), $userId);
$posts = getUserPosts(db(), $userId, 10);
$activeTab = $_GET['tab'] ?? 'posts';
$validTabs = ['posts', 'games', 'achievements', 'about'];

if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'posts';
}

$isOwnProfile = true;

$name = (string) $user['display_name'];

$initials = strtoupper(
    substr($name, 0, 1) .
    substr((string) $user['username'], 0, 1)
);

$avatar = $user['avatar_url']
    ?: $defaultAvatar;

$profileUrl = appUrl('@' . $user['username']);
$socialUrl = static function (?string $url): string {
    $url = trim((string) $url);
    if ($url !== '' && !preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
        return 'https://' . $url;
    }

    return $url;
};
$socialLinks = [
    'discord' => ['label' => 'Discord', 'icon' => 'discord'],
    'tiktok' => ['label' => 'TikTok', 'icon' => 'tiktok'],
    'facebook' => ['label' => 'Facebook', 'icon' => 'facebook'],
    'twitch' => ['label' => 'Twitch', 'icon' => 'twitch'],
    'kick' => ['label' => 'Kick', 'icon' => 'kick'],
    'github' => ['label' => 'GitHub', 'icon' => 'github'],
    'spotify' => ['label' => 'Spotify', 'icon' => 'spotify'],
    'youtube' => ['label' => 'YouTube', 'icon' => 'youtube'],
    'apple_music' => ['label' => 'Apple Music', 'icon' => 'applemusic'],
    'steam' => ['label' => 'Steam', 'icon' => 'steam'],
    'x' => ['label' => 'X', 'icon' => 'x'],
];
$dashboardLayout = true;
$dashboardActivePage = 'profile';

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
            appUrl('assets/css/shared/layout.css'),
            ENT_QUOTES,
            'UTF-8'
        ) ?>"
    >

    <link
        rel="stylesheet"
        href="<?= htmlspecialchars(
            appUrl('assets/css/profile.css'),
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

        <div class="profile-sticky-bar" data-profile-sticky aria-hidden="true">
            <a
                class="profile-sticky-person"
                href="#top"
                aria-label="Back to profile"
            >
                <img
                    class="profile-sticky-avatar"
                    src="<?php echo htmlspecialchars($user['avatar_url'] ?: $defaultAvatar, ENT_QUOTES, 'UTF-8'); ?>"
                    alt=""
                >
                <strong><?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
            </a>

            <div class="profile-sticky-stats" aria-label="Profile statistics">
                <span>
                    <strong><?php echo number_format((int) $stats['friends']); ?></strong>
                    <small>Friends</small>
                </span>
                <span>
                    <strong><?php echo number_format((int) $stats['followers']); ?></strong>
                    <small>Followers</small>
                </span>
                <span>
                    <strong><?php echo number_format((int) $stats['following']); ?></strong>
                    <small>Following</small>
                </span>
            </div>
        </div>

        <main class="page-content profile-page" id="top">
        <header class="profile-hero" data-profile-hero>
            <div class="profile-cover">
                <?php if (!empty($user['cover_url'])): ?>
                    <img src="<?php echo htmlspecialchars($user['cover_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover photo">
                <?php else: ?>
                    <div class="profile-cover-fallback"></div>
                <?php endif; ?>
            </div>

            <div class="profile-hero-body">
                <div class="profile-person-column">
                    <div class="profile-person">
                        <div class="profile-avatar-wrap">
                            <img class="profile-avatar" src="<?php echo htmlspecialchars($user['avatar_url'] ?: $defaultAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="profile-identity">
                        <h1><?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></h1>

                        <div class="profile-handle-row">
                            <span class="profile-handle">@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <button class="copy-user-btn" type="button" data-copy-user="@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" aria-label="Copy username">
                                <span class="material-symbols-rounded" aria-hidden="true">content_copy</span>
                            </button>
                        </div>

                        <div class="profile-badges">
                            <?php if (!empty($user['gaming_style'])): ?>
                                <span class="meta-pill"><span class="material-symbols-rounded" aria-hidden="true">sports_esports</span> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['gaming_style'])), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($user['online_status'])): ?>
                                <span class="meta-pill meta-pill-status status-<?php echo htmlspecialchars(strtolower($user['online_status']), ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="material-symbols-rounded" aria-hidden="true">circle</span> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['online_status'])), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="profile-actions-wrapper">
                            <?php if ($isOwnProfile): ?>
                                <button class="primary-button" type="button"><span class="material-symbols-rounded" aria-hidden="true">edit</span> Edit Profile</button>
                            <?php else: ?>
                                <button class="primary-button" type="button"><span class="material-symbols-rounded" aria-hidden="true">person_add</span> Follow</button>
                            <?php endif; ?>
                        </div>

                        </div>
                    </div>

                    <?php if (!empty($user['bio'])): ?>
                        <p class="profile-bio"><?php echo nl2br(htmlspecialchars($user['bio'], ENT_QUOTES, 'UTF-8')); ?></p>
                    <?php endif; ?>

                    <?php if (array_filter(array_map(static fn ($key) => $user['social_' . $key] ?? null, array_keys($socialLinks)))): ?>
                        <div class="profile-socials" aria-label="Social links">
                            <?php foreach ($socialLinks as $socialKey => $social): ?>
                                <?php if (!empty($user['social_' . $socialKey])): ?>
                                    <a class="profile-social-link" href="<?php echo htmlspecialchars($socialUrl($user['social_' . $socialKey]), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" aria-label="Open <?php echo htmlspecialchars($social['label'], ENT_QUOTES, 'UTF-8'); ?> profile" title="Open <?php echo htmlspecialchars($social['label'], ENT_QUOTES, 'UTF-8'); ?> profile">
                                        <img class="profile-social-mark" src="https://cdn.simpleicons.org/<?php echo htmlspecialchars($social['icon'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    </div>

                <div class="profile-stats" aria-label="Profile statistics">
                    <div class="stat-item">
                        <strong><?php echo number_format((int) $stats['friends']); ?></strong>
                        <span>Friends</span>
                    </div>
                    <div class="stat-item">
                        <strong><?php echo number_format((int) $stats['followers']); ?></strong>
                        <span>Followers</span>
                    </div>
                    <div class="stat-item">
                        <strong><?php echo number_format((int) $stats['following']); ?></strong>
                        <span>Following</span>
                    </div>
                </div>
            </div>
        </header>

        <nav class="profile-tabs" aria-label="Profile tabs">
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=posts" class="tab-item <?php echo $activeTab === 'posts' ? 'active' : ''; ?>" data-tab="posts">
                <span class="material-symbols-rounded" aria-hidden="true">edit_note</span> Posts
            </a>
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=games" class="tab-item <?php echo $activeTab === 'games' ? 'active' : ''; ?>" data-tab="games">
                <span class="material-symbols-rounded" aria-hidden="true">sports_esports</span> Games
            </a>
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=achievements" class="tab-item <?php echo $activeTab === 'achievements' ? 'active' : ''; ?>" data-tab="achievements">
                <span class="material-symbols-rounded" aria-hidden="true">trophy</span> Achievements
            </a>
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=about" class="tab-item <?php echo $activeTab === 'about' ? 'active' : ''; ?>" data-tab="about">
                <span class="material-symbols-rounded" aria-hidden="true">info</span> About
            </a>
        </nav>

        <div class="profile-main-content">
            <section class="profile-panel <?php echo $activeTab === 'posts' ? 'active' : ''; ?>" id="tab-posts">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><span class="material-symbols-rounded" aria-hidden="true">inbox</span></div>
                        <h3>No public posts yet</h3>
                        <p>Share your latest gaming moment and it will appear here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                        <?php $postImages = array_slice($post['images'] ?? [], 0, 4); ?>
                        <article class="post-card">
                            <div class="post-header">
                                <img class="post-avatar" src="<?php echo htmlspecialchars($user['avatar_url'] ?: $defaultAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="post-person">
                                    <strong><?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <span>@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                                <time><?php echo htmlspecialchars(timeAgo($post['created_at']), ENT_QUOTES, 'UTF-8'); ?></time>
                            </div>

                            <div class="post-body">
                                <p><?php echo nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')); ?></p>

                                <?php if (!empty($postImages)): ?>
                                    <div class="post-media media-count-<?php echo count($postImages); ?>">
                                        <?php foreach ($postImages as $imageIndex => $imageUrl): ?>
                                            <div class="media-item">
                                                <img src="<?php echo htmlspecialchars($imageUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Post image <?php echo ($imageIndex + 1); ?>">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="post-metrics">
                                <span><span class="material-symbols-rounded" aria-hidden="true">favorite</span> <?php echo number_format((int) ($post['like_count'] ?? 0)); ?> Likes</span>
                                <span><span class="material-symbols-rounded" aria-hidden="true">comment</span> <?php echo number_format((int) ($post['comment_count'] ?? 0)); ?> Comments</span>
                                <span><span class="material-symbols-rounded" aria-hidden="true">share</span> <?php echo number_format((int) ($post['share_count'] ?? 0)); ?> Shares</span>
                            </div>

                            <div class="post-comments">
                                <?php if (!empty($post['comments'])): ?>
                                    <?php foreach (array_slice($post['comments'], 0, 2) as $comment): ?>
                                        <div class="comment-item">
                                            <div class="comment-avatar">
                                                <?php if (!empty($comment['avatar_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($comment['avatar_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($comment['display_name'] ?: $comment['username'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php else: ?>
                                                    <span><?php echo htmlspecialchars(strtoupper(substr(($comment['display_name'] ?: $comment['username'] ?: 'G'), 0, 1)), ENT_QUOTES, 'UTF-8'); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="comment-copy">
                                                <strong><?php echo htmlspecialchars($comment['display_name'] ?: $comment['username'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <p><?php echo htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8'); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="comment-empty">No comments yet.</div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

            <section class="profile-panel <?php echo $activeTab === 'games' ? 'active' : ''; ?>" id="tab-games">
                <div class="coming-soon-card">
                    <div class="coming-soon-icon"><span class="material-symbols-rounded" aria-hidden="true">sports_esports</span></div>
                    <h3>Games tab is coming soon</h3>
                    <p>This section will show the profile's full game library and rankings.</p>
                </div>
            </section>

            <section class="profile-panel <?php echo $activeTab === 'achievements' ? 'active' : ''; ?>" id="tab-achievements">
                <div class="coming-soon-card">
                    <div class="coming-soon-icon"><span class="material-symbols-rounded" aria-hidden="true">trophy</span></div>
                    <h3>Achievements tab is coming soon</h3>
                    <p>Achievement milestones and trophy progress will appear here.</p>
                </div>
            </section>

            <section class="profile-panel <?php echo $activeTab === 'about' ? 'active' : ''; ?>" id="tab-about">
                <div class="coming-soon-card">
                    <div class="coming-soon-icon"><span class="material-symbols-rounded" aria-hidden="true">info</span></div>
                    <h3>About tab is coming soon</h3>
                    <p>Bio, details, and profile information will be organized here.</p>
                </div>
            </section>
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

    <script src="<?= htmlspecialchars(
        appUrl('assets/js/profile.js'),
        ENT_QUOTES,
        'UTF-8'
    ) ?>" defer></script>

    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

</body>

</html>

<?php
function timeAgo(string $dateString): string
{
    $date = new DateTime($dateString);
    $now = new DateTime();
    $interval = $now->diff($date);

    if ($interval->y > 0) {
        return $interval->y . 'y ago';
    }
    if ($interval->m > 0) {
        return $interval->m . 'mo ago';
    }
    if ($interval->d > 0) {
        return $interval->d . 'd ago';
    }
    if ($interval->h > 0) {
        return $interval->h . 'h ago';
    }
    if ($interval->i > 0) {
        return $interval->i . 'm ago';
    }

    return max(0, $interval->s) . 's ago';
}
?>