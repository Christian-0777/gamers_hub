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

$userId = (int) $_SESSION['user_id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$postError = null;
$activeFeed = (string) ($_GET['feed'] ?? 'your');
$validFeedTabs = ['your', 'following', 'friends'];

if (!in_array($activeFeed, $validFeedTabs, true)) {
    $activeFeed = 'your';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_post') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $content = trim((string) ($_POST['content'] ?? ''));

    if (empty($_SESSION['csrf_token']) || $csrfToken === '' || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $postError = 'Your session expired. Please try again.';
    } elseif ($content === '') {
        $postError = 'Write something before publishing your post.';
    } elseif (mb_strlen($content) > 5000) {
        $postError = 'Posts must be 5,000 characters or fewer.';
    } else {
        $postStatement = db()->prepare(
            'INSERT INTO posts (user_id, post_type, content, visibility, status)
             VALUES (:user_id, \'text\', :content, \'public\', \'published\')'
        );
        $postStatement->execute([
            'user_id' => $userId,
            'content' => $content,
        ]);

        header('Location: ' . appUrl('home') . '?posted=1');
        exit;
    }
}

$feedFilter = '';
$feedParameters = [];

if ($activeFeed === 'following') {
    $feedFilter = '
     AND (
         EXISTS (
             SELECT 1
             FROM followers following
             WHERE following.follower_id = :following_user_id
               AND following.following_id = p.user_id
         )
         OR EXISTS (
             SELECT 1
             FROM followers friend_outgoing
             INNER JOIN followers friend_incoming
                 ON friend_incoming.follower_id = friend_outgoing.following_id
                AND friend_incoming.following_id = friend_outgoing.follower_id
             WHERE friend_outgoing.follower_id = :following_friend_user_id
               AND friend_outgoing.following_id = p.user_id
         )
     )';
    $feedParameters = [
        'following_user_id' => $userId,
        'following_friend_user_id' => $userId,
    ];
} elseif ($activeFeed === 'friends') {
    $feedFilter = '
     AND EXISTS (
         SELECT 1
         FROM followers friend_outgoing
         INNER JOIN followers friend_incoming
             ON friend_incoming.follower_id = friend_outgoing.following_id
            AND friend_incoming.following_id = friend_outgoing.follower_id
         WHERE friend_outgoing.follower_id = :friends_user_id
           AND friend_outgoing.following_id = p.user_id
     )';
    $feedParameters = [
        'friends_user_id' => $userId,
    ];
}

$feedStatement = db()->prepare(
    'SELECT
        p.id,
        p.user_id,
        p.content,
        p.created_at,
        u.username,
        up.display_name,
        up.avatar_url,
        (SELECT COUNT(*) FROM post_reactions pr WHERE pr.post_id = p.id AND pr.reaction_type = \'like\') AS like_count,
        (SELECT COUNT(*) FROM comments c WHERE c.post_id = p.id AND c.status = \'published\') AS comment_count,
        (SELECT COUNT(*) FROM post_shares ps WHERE ps.post_id = p.id) AS share_count,
        (SELECT pm.media_url FROM post_media pm WHERE pm.post_id = p.id AND pm.media_type = \'image\' ORDER BY pm.sort_order ASC, pm.id ASC LIMIT 1) AS media_url
     FROM posts p
     INNER JOIN users u ON u.id = p.user_id AND u.status = \'active\'
     LEFT JOIN user_profiles up ON up.user_id = u.id
     WHERE p.visibility = \'public\' AND p.status = \'published\'
    ' . $feedFilter . '
     ORDER BY p.created_at DESC, p.id DESC
     LIMIT 30'
);
$feedStatement->execute($feedParameters);
$feedPosts = $feedStatement->fetchAll();

$timeAgo = static function (string $dateString): string {
    $seconds = max(0, time() - strtotime($dateString));

    if ($seconds < 60) {
        return 'Just now';
    }

    if ($seconds < 3600) {
        return floor($seconds / 60) . 'm';
    }

    if ($seconds < 86400) {
        return floor($seconds / 3600) . 'h';
    }

    if ($seconds < 604800) {
        return floor($seconds / 86400) . 'd';
    }

    return date('M j', strtotime($dateString));
};

$name = (string) $user['display_name'];

$initials = strtoupper(
    substr($name, 0, 1) .
    substr((string) $user['username'], 0, 1)
);

$avatar = $user['avatar_url']
    ?: appUrl('assets/icons/profile.png');

$profileUrl = appUrl('@' . $user['username']);
$dashboardLayout = true;
$dashboardActivePage = 'home';

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

    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/coming_soon.css'), ENT_QUOTES, 'UTF-8') ?>">

    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/home.css'), ENT_QUOTES, 'UTF-8') ?>">

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

            <div class="feed-heading">
                <div>
                    <span class="feed-eyebrow">Community pulse</span>
                    <h1>Your feed</h1>
                    <p>Share a gaming moment or catch up with what your circle is playing.</p>
                </div>
            </div>

            <nav class="feed-tabs" aria-label="Feed views">
                <a class="feed-tab <?= $activeFeed === 'your' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(appUrl('home') . '?feed=your', ENT_QUOTES, 'UTF-8') ?>">
                    <span class="material-symbols-rounded" aria-hidden="true">dynamic_feed</span>
                    Your Feed
                </a>
                <a class="feed-tab <?= $activeFeed === 'following' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(appUrl('home') . '?feed=following', ENT_QUOTES, 'UTF-8') ?>">
                    <span class="material-symbols-rounded" aria-hidden="true">person_add</span>
                    Following
                </a>
                <a class="feed-tab <?= $activeFeed === 'friends' ? 'is-active' : '' ?>" href="<?= htmlspecialchars(appUrl('home') . '?feed=friends', ENT_QUOTES, 'UTF-8') ?>">
                    <span class="material-symbols-rounded" aria-hidden="true">group</span>
                    Friends
                </a>
            </nav>

            <?php if (isset($_GET['posted'])): ?>
                <div class="feed-alert feed-alert-success" role="status">Your post is live.</div>
            <?php elseif ($postError !== null): ?>
                <div class="feed-alert feed-alert-error" role="alert"><?= htmlspecialchars($postError, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <div class="feed-layout">
                <section class="feed-stream" aria-label="Your feed">
                    <form class="feed-composer dashboard-card" method="post">
                        <input type="hidden" name="action" value="create_post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="feed-composer-top">
                            <img class="feed-avatar" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <textarea name="content" rows="2" maxlength="5000" placeholder="What's happening in your gaming world, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>?" aria-label="Post content"></textarea>
                        </div>
                        <div class="feed-composer-footer">
                            <span class="feed-composer-hint"><span class="material-symbols-rounded" aria-hidden="true">public</span> Public post</span>
                            <button class="feed-publish-button" type="submit"><span class="material-symbols-rounded" aria-hidden="true">send</span> Publish</button>
                        </div>
                    </form>

                    <?php if (empty($feedPosts)): ?>
                        <div class="dashboard-card feed-empty-state">
                            <span class="material-symbols-rounded" aria-hidden="true">forum</span>
                            <h2>Your feed is waiting</h2>
                            <p>Be the first to share a gaming update with the community.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feedPosts as $post): ?>
                            <?php
                            $postName = (string) ($post['display_name'] ?: $post['username']);
                            $postAvatar = $post['avatar_url'] ?: appUrl('assets/icons/profile.png');
                            ?>
                            <article class="dashboard-card feed-post" data-post-id="<?= (int) $post['id'] ?>" data-search-text="<?= htmlspecialchars(strtolower($postName . ' ' . $post['content']), ENT_QUOTES, 'UTF-8') ?>">
                                <header class="feed-post-header">
                                    <img class="feed-avatar" src="<?= htmlspecialchars($postAvatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    <div class="feed-post-author">
                                        <strong><?= htmlspecialchars($postName, ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span>@<?= htmlspecialchars($post['username'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($timeAgo((string) $post['created_at']), ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                    <button class="feed-more-button" type="button" aria-label="More post options"><span class="material-symbols-rounded" aria-hidden="true">more_horiz</span></button>
                                </header>
                                <div class="feed-post-body">
                                    <p><?= nl2br(htmlspecialchars($post['content'], ENT_QUOTES, 'UTF-8')) ?></p>
                                    <?php if (!empty($post['media_url'])): ?>
                                        <img class="feed-post-media" src="<?= htmlspecialchars($post['media_url'], ENT_QUOTES, 'UTF-8') ?>" alt="Post attachment">
                                    <?php endif; ?>
                                </div>
                                <div class="feed-post-metrics">
                                    <span><span class="material-symbols-rounded" aria-hidden="true">favorite</span> <b class="feed-like-count"><?= number_format((int) $post['like_count']) ?></b> likes</span>
                                    <span><?= number_format((int) $post['comment_count']) ?> comments · <?= number_format((int) $post['share_count']) ?> shares</span>
                                </div>
                                <div class="feed-post-actions">
                                    <button class="feed-post-action feed-like-button" type="button"><span class="material-symbols-rounded" aria-hidden="true">favorite</span> Like</button>
                                    <button class="feed-post-action" type="button"><span class="material-symbols-rounded" aria-hidden="true">comment</span> Comment</button>
                                    <button class="feed-post-action" type="button"><span class="material-symbols-rounded" aria-hidden="true">share</span> Share</button>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
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

    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

    <script src="<?= htmlspecialchars(appUrl('assets/js/home.js'), ENT_QUOTES, 'UTF-8') ?>"></script>

</body>

</html>