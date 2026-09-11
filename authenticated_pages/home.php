<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';
require_once __DIR__ . '/../config/storage.php';

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
$postTypes = [
    'text' => 'General update',
    'discussion' => 'Discussion',
    'question' => 'Question',
    'looking_for_players' => 'Looking for players',
    'achievement' => 'Achievement',
    'review' => 'Review',
];
$visibilityOptions = [
    'public' => 'Public',
    'friends' => 'Friends',
    'followers' => 'Followers',
    'private' => 'Only me',
];
$activeFeed = (string) ($_GET['feed'] ?? 'your');
$validFeedTabs = ['your', 'following', 'friends'];

if (!in_array($activeFeed, $validFeedTabs, true)) {
    $activeFeed = 'your';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_post') {
    $csrfToken = (string) ($_POST['csrf_token'] ?? '');
    $content = trim((string) ($_POST['content'] ?? ''));
    $visibility = (string) ($_POST['visibility'] ?? 'public');
    $postType = (string) ($_POST['post_type'] ?? 'text');
    $gameId = (int) ($_POST['game_id'] ?? 0);
    $uploadedFiles = (array) ($_FILES['media'] ?? []);
    $mediaFiles = [];

    foreach (($uploadedFiles['error'] ?? []) as $index => $uploadError) {
        if ($uploadError === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $mediaFiles[] = [
            'name' => (string) ($uploadedFiles['name'][$index] ?? ''),
            'tmp_name' => (string) ($uploadedFiles['tmp_name'][$index] ?? ''),
            'error' => (int) $uploadError,
            'size' => (int) ($uploadedFiles['size'][$index] ?? 0),
        ];
    }

    if (empty($_SESSION['csrf_token']) || $csrfToken === '' || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $postError = 'Your session expired. Please try again.';
    } elseif ($content === '') {
        $postError = 'Write something before publishing your post.';
    } elseif (mb_strlen($content) > 5000) {
        $postError = 'Posts must be 5,000 characters or fewer.';
    } elseif (!isset($visibilityOptions[$visibility])) {
        $postError = 'Choose a valid audience for your post.';
    } elseif (!isset($postTypes[$postType])) {
        $postError = 'Choose a valid post type.';
    } elseif (count($mediaFiles) > 10) {
        $postError = 'You can attach up to 10 files to one post.';
    } else {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            if ($gameId > 0) {
                $gameStatement = $pdo->prepare("SELECT id FROM games WHERE id = :id AND status = 'active' LIMIT 1");
                $gameStatement->execute(['id' => $gameId]);
                if (!$gameStatement->fetch()) {
                    $gameId = 0;
                }
            }

            $postStatement = $pdo->prepare(
                'INSERT INTO posts (user_id, game_id, post_type, content, visibility, status)
                 VALUES (:user_id, :game_id, :post_type, :content, :visibility, \'published\')'
            );
            $postStatement->execute([
                'user_id' => $userId,
                'game_id' => $gameId > 0 ? $gameId : null,
                'post_type' => $postType,
                'content' => $content,
                'visibility' => $visibility,
            ]);
            $postId = (int) $pdo->lastInsertId();

            if ($mediaFiles !== []) {
                $usernameSlug = preg_replace('/[^a-zA-Z0-9_-]+/', '_', (string) $user['username']) ?: 'user';
                $relativeFolder = count($mediaFiles) >= 2 ? 'post/' . $usernameSlug . '_' . $postId : 'post';
                $uploadRoot = dirname(__DIR__) . '/uploads/' . $relativeFolder;
                if (!is_dir($uploadRoot) && !mkdir($uploadRoot, 0775, true) && !is_dir($uploadRoot)) {
                    throw new RuntimeException('Unable to create the post upload folder.');
                }

                $mediaStatement = $pdo->prepare(
                    'INSERT INTO post_media (post_id, media_type, media_url, file_size, width, height, duration_seconds, sort_order)
                     VALUES (:post_id, :media_type, :media_url, :file_size, :width, :height, :duration_seconds, :sort_order)'
                );

                foreach ($mediaFiles as $sortOrder => $file) {
                    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
                        throw new RuntimeException('One of the selected files could not be uploaded.');
                    }

                    $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
                    $mediaType = str_starts_with((string) $mimeType, 'image/') ? 'image' : (str_starts_with((string) $mimeType, 'video/') ? 'video' : null);
                    if ($mediaType === null || $file['size'] > 100 * 1024 * 1024) {
                        throw new RuntimeException('Only images and videos up to 100 MB are allowed.');
                    }

                    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                    $safeExtension = preg_replace('/[^a-z0-9]/', '', $extension) ?: ($mediaType === 'image' ? 'jpg' : 'mp4');
                    $filename = ($sortOrder + 1) . '_' . bin2hex(random_bytes(8)) . '.' . $safeExtension;
                    $targetPath = $uploadRoot . DIRECTORY_SEPARATOR . $filename;
                    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                        throw new RuntimeException('Unable to save one of the selected files.');
                    }

                    $width = null;
                    $height = null;
                    if ($mediaType === 'image') {
                        $dimensions = @getimagesize($targetPath);
                        $width = $dimensions[0] ?? null;
                        $height = $dimensions[1] ?? null;
                    }

                    $mediaUrl = rtrim(appUrl('uploads'), '/') . '/' . implode('/', array_map('rawurlencode', explode('/', $relativeFolder))) . '/' . rawurlencode($filename);
                    $mediaStatement->execute([
                        'post_id' => $postId,
                        'media_type' => $mediaType,
                        'media_url' => $mediaUrl,
                        'file_size' => $file['size'],
                        'width' => $width,
                        'height' => $height,
                        'duration_seconds' => null,
                        'sort_order' => $sortOrder,
                    ]);
                }
            }

            $pdo->commit();
            header('Location: ' . appUrl('home') . '?posted=1');
            exit;
        } catch (Throwable $exception) {
            $pdo->rollBack();
            $postError = $exception->getMessage();
        }
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
         WHERE p.status = \'published\'
             AND (
                     p.user_id = :feed_owner_id
                     OR p.visibility = \'public\'
                     OR (p.visibility = \'followers\' AND EXISTS (SELECT 1 FROM followers f WHERE f.follower_id = :feed_follower_id AND f.following_id = p.user_id))
                     OR (p.visibility = \'friends\' AND EXISTS (SELECT 1 FROM followers fo INNER JOIN followers fi ON fi.follower_id = fo.following_id AND fi.following_id = fo.follower_id WHERE fo.follower_id = :feed_friend_id AND fo.following_id = p.user_id))
             )
    ' . $feedFilter . '
     ORDER BY p.created_at DESC, p.id DESC
     LIMIT 30'
);
$feedParameters = array_merge([
    'feed_owner_id' => $userId,
    'feed_follower_id' => $userId,
    'feed_friend_id' => $userId,
], $feedParameters);
$feedStatement->execute($feedParameters);
$feedPosts = $feedStatement->fetchAll();

$gameStatement = db()->query("SELECT id, name FROM games WHERE status = 'active' ORDER BY name ASC");
$games = $gameStatement->fetchAll();

$mediaStatement = db()->prepare(
    'SELECT media_type, media_url, thumbnail_url
     FROM post_media
     WHERE post_id = :post_id
     ORDER BY sort_order ASC, id ASC'
);

foreach ($feedPosts as $index => $feedPost) {
    $mediaStatement->execute(['post_id' => $feedPost['id']]);
    $feedPosts[$index]['media'] = $mediaStatement->fetchAll();
}

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
                    <button class="feed-composer dashboard-card" type="button" data-post-modal-open>
                        <span class="feed-composer-top">
                            <img class="feed-avatar" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
                            <span class="feed-composer-placeholder">What's happening in your gaming world, <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>?</span>
                        </span>
                        <span class="feed-composer-footer">
                            <span class="feed-composer-hint"><span class="material-symbols-rounded" aria-hidden="true">add_photo_alternate</span> Create a post</span>
                            <span class="feed-publish-button"><span class="material-symbols-rounded" aria-hidden="true">edit</span> Compose</span>
                        </span>
                    </button>

                    <div class="post-modal" data-post-modal hidden>
                        <div class="post-modal-backdrop" data-post-modal-close></div>
                        <section class="post-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="postModalTitle">
                            <form class="post-form" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="create_post">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                <header class="post-modal-header">
                                    <h2 id="postModalTitle">Create a post</h2>
                                    <button class="post-modal-close" type="button" data-post-modal-close aria-label="Close post composer"><span class="material-symbols-rounded" aria-hidden="true">close</span></button>
                                </header>
                                <div class="post-author-row">
                                    <img class="feed-avatar" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt="">
                                    <div><strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong><span>@<?= htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') ?></span></div>
                                </div>
                                <div class="post-form-options">
                                    <label>Audience<select name="visibility">
                                        <?php foreach ($visibilityOptions as $value => $label): ?><option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                                    </select></label>
                                    <label>Game<select name="game_id"><option value="0">No game</option><?php foreach ($games as $game): ?><option value="<?= (int) $game['id'] ?>"><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                                    <label>Post type<select name="post_type"><?php foreach ($postTypes as $value => $label): ?><option value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
                                </div>
                                <textarea name="content" rows="6" maxlength="5000" placeholder="Share something with the community..." required></textarea>
                                <label class="post-upload-control"><span class="material-symbols-rounded" aria-hidden="true">perm_media</span><span>Add photos or video clips</span><small>Up to 10 files. Videos must be 5 minutes or shorter.</small><input type="file" name="media[]" accept="image/*,video/*" multiple data-post-media></label>
                                <div class="post-media-preview" data-post-media-preview></div>
                                <footer class="post-modal-footer"><span>Images and videos are checked before publishing.</span><button class="feed-publish-button" type="submit"><span class="material-symbols-rounded" aria-hidden="true">send</span> Publish</button></footer>
                            </form>
                        </section>
                    </div>

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
                                    <?php if (!empty($post['media'])): ?>
                                        <div class="feed-post-gallery feed-post-gallery-count-<?= min(4, count($post['media'])) ?>">
                                            <?php foreach ($post['media'] as $media): ?>
                                                <?php if ($media['media_type'] === 'video'): ?><video class="feed-post-media" controls preload="metadata" src="<?= htmlspecialchars($media['media_url'], ENT_QUOTES, 'UTF-8') ?>"></video><?php else: ?><img class="feed-post-media" src="<?= htmlspecialchars($media['media_url'], ENT_QUOTES, 'UTF-8') ?>" alt="Post attachment"><?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
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