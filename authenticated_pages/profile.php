<?php
/**
 * User Profile Page
 * Gamers Hub Platform
 * Route: /gamers_hub/@username
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';
require_once __DIR__ . '/../api/profile_fetch.php';
require_once __DIR__ . '/../api/post_fetch.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$pdo = db();
$username = isset($_GET['username']) ? trim((string) $_GET['username']) : null;

if (!$username) {
    header('Location: /gamers_hub');
    exit;
}

$user = getUserByUsername($pdo, $username);
if (!$user) {
    http_response_code(404);
    die('User not found');
}

$user_id = (int) $user['id'];
$defaultAvatar = appUrl('assets/icons/profile.png');
$stats = getUserStats($pdo, $user_id);
$games = getUserGames($pdo, $user_id, 3);
$platforms = getUserPlatforms($pdo, $user_id);
$friends = getUserFriends($pdo, $user_id, 3);
$posts = getUserPosts($pdo, $user_id, 10);

$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'posts';
$validTabs = ['posts', 'games', 'achievements', 'about'];
if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'posts';
}

$isOwnProfile = true;
$isLoggedIn = !empty($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?> - Gamers Hub</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('assets/css/style.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('assets/css/shared/header.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('assets/css/shared/sidebar.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('assets/css/shared/footer.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="<?php echo htmlspecialchars(appUrl('assets/css/profile.css'), ENT_QUOTES, 'UTF-8'); ?>">
    <script src="<?php echo htmlspecialchars(appUrl('assets/js/profile.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
</head>
<body data-username="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>">
<div class="app-shell">
    <?php require __DIR__ . '/../includes/shared_header.php'; ?>
    <?php require __DIR__ . '/../includes/shared_sidebar.php'; ?>

    <main class="page-content profile-page">
        <header class="profile-hero" data-profile-hero>
            <div class="profile-cover">
                <?php if (!empty($user['cover_url'])): ?>
                    <img src="<?php echo htmlspecialchars($user['cover_url'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover photo">
                <?php else: ?>
                    <div class="profile-cover-fallback"></div>
                <?php endif; ?>
            </div>

            <div class="profile-hero-body">
                <div class="profile-person">
                    <div class="profile-avatar-wrap">
                        <img class="profile-avatar" src="<?php echo htmlspecialchars($user['avatar_url'] ?: $defaultAvatar, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="profile-identity">
                        <h1><?php echo htmlspecialchars($user['display_name'], ENT_QUOTES, 'UTF-8'); ?></h1>

                        <div class="profile-handle-row">
                            <span class="profile-handle">@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <button class="copy-user-btn" type="button" data-copy-user="@<?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?>" aria-label="Copy username">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>

                        <div class="profile-badges">
                            <?php if (!empty($user['gaming_style'])): ?>
                                <span class="meta-pill"><i class="fas fa-gamepad"></i> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['gaming_style'])), ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($user['online_status'])): ?>
                                <span class="meta-pill meta-pill-status status-<?php echo htmlspecialchars(strtolower($user['online_status']), ENT_QUOTES, 'UTF-8'); ?>">
                                    <i class="fas fa-circle"></i> <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['online_status'])), ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="profile-actions-wrapper">
                            <?php if ($isOwnProfile): ?>
                                <button class="primary-button" type="button"><i class="fas fa-pen"></i> Edit Profile</button>
                            <?php else: ?>
                                <button class="primary-button" type="button"><i class="fas fa-user-plus"></i> Follow</button>
                            <?php endif; ?>
                        </div>
                    </div>
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
                <i class="fas fa-pen-fancy"></i> Posts
            </a>
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=games" class="tab-item <?php echo $activeTab === 'games' ? 'active' : ''; ?>" data-tab="games">
                <i class="fas fa-gamepad"></i> Games
            </a>
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=achievements" class="tab-item <?php echo $activeTab === 'achievements' ? 'active' : ''; ?>" data-tab="achievements">
                <i class="fas fa-trophy"></i> Achievements
            </a>
            <a href="?username=<?php echo rawurlencode($username); ?>&tab=about" class="tab-item <?php echo $activeTab === 'about' ? 'active' : ''; ?>" data-tab="about">
                <i class="fas fa-circle-info"></i> About
            </a>
        </nav>

        <div class="profile-main-content">
            <section class="profile-panel <?php echo $activeTab === 'posts' ? 'active' : ''; ?>" id="tab-posts">
                <?php if (empty($posts)): ?>
                    <div class="empty-state">
                        <div class="empty-icon"><i class="fas fa-inbox"></i></div>
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
                                <span><i class="fas fa-heart"></i> <?php echo number_format((int) ($post['like_count'] ?? 0)); ?> Likes</span>
                                <span><i class="fas fa-comment"></i> <?php echo number_format((int) ($post['comment_count'] ?? 0)); ?> Comments</span>
                                <span><i class="fas fa-share"></i> <?php echo number_format((int) ($post['share_count'] ?? 0)); ?> Shares</span>
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
                    <div class="coming-soon-icon"><i class="fas fa-gamepad"></i></div>
                    <h3>Games tab is coming soon</h3>
                    <p>This section will show the profile's full game library and rankings.</p>
                </div>
            </section>

            <section class="profile-panel <?php echo $activeTab === 'achievements' ? 'active' : ''; ?>" id="tab-achievements">
                <div class="coming-soon-card">
                    <div class="coming-soon-icon"><i class="fas fa-trophy"></i></div>
                    <h3>Achievements tab is coming soon</h3>
                    <p>Achievement milestones and trophy progress will appear here.</p>
                </div>
            </section>

            <section class="profile-panel <?php echo $activeTab === 'about' ? 'active' : ''; ?>" id="tab-about">
                <div class="coming-soon-card">
                    <div class="coming-soon-icon"><i class="fas fa-circle-info"></i></div>
                    <h3>About tab is coming soon</h3>
                    <p>Bio, details, and profile information will be organized here.</p>
                </div>
            </section>
        </div>
    </main>

    <?php require __DIR__ . '/../includes/shared_footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo htmlspecialchars(appUrl('assets/js/shared/header.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars(appUrl('assets/js/shared/sidebar.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars(appUrl('assets/js/shared/footer.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars(appUrl('assets/js/layout_functions.js'), ENT_QUOTES, 'UTF-8'); ?>"></script>
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

    return $interval->s . 's ago';
}
?>

