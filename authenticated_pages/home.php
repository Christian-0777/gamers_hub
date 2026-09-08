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
    'SELECT user_id FROM sessions WHERE id = :id AND user_id = :user_id AND expires_at > NOW() LIMIT 1'
);
$sessionStatement->execute([
    'id' => $_SESSION['auth_session_id'],
    'user_id' => $_SESSION['user_id'],
]);

if (!$sessionStatement->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['auth_session_id']);
    header('Location: ' . appUrl('login'));
    exit;
}

$games = [
    'Valorant',
    'Counter-Strike 2',
    'Minecraft',
    'Grand Theft Auto V',
    'Dota 2',
    'League of Legends',
    'Call of Duty',
    'Euro Truck Simulator 2',
];

$posts = [
    [
        'author' => 'Maya Chen',
        'handle' => '@mayac',
        'initials' => 'MC',
        'tone' => 'coral',
        'time' => '18 min ago',
        'type' => 'ACHIEVEMENT',
        'game' => 'VALORANT',
        'content' => 'Finally hit Ascendant after two weeks of late-night queues. The grind was worth it.',
        'image' => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1200&q=80',
        'likes' => '248',
        'comments' => '42',
        'shares' => '18',
        'tag' => '#VALORANT',
    ],
    [
        'author' => 'Rafael Torres',
        'handle' => '@rafatorres',
        'initials' => 'RT',
        'tone' => 'blue',
        'time' => '1 hr ago',
        'type' => 'LFG',
        'game' => 'MINECRAFT',
        'content' => 'Building a small survival server tonight. Looking for a few chill players who like exploring and making things pretty.',
        'image' => '',
        'likes' => '86',
        'comments' => '13',
        'shares' => '9',
        'tag' => '#LFG',
    ],
];

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>GamersHUB | Your gaming community</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/style.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/header.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/sidebar.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/footer.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>

<body>

<div class="app-shell">

    <?php require __DIR__ . '/../includes/shared_header.php'; ?>

    <?php require __DIR__ . '/../includes/shared_sidebar.php'; ?>

    <main class="page-content" id="feed">

        <section class="content-column">

            <div class="page-heading">
                <div>
                    <p class="eyebrow">TUESDAY, SEPTEMBER 09</p>

                    <h1>Your feed</h1>

                    <p class="heading-subtitle">
                        The latest from your gaming circle.
                    </p>
                </div>

                <button class="filter-button" type="button">
                    Latest
                    <span>⌄</span>
                </button>
            </div>

            <section class="composer-card" aria-label="Create a post">

                <div class="composer-row">
                    <span class="avatar avatar-coral">AK</span>

                    <button
                        class="composer-input"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#postModal"
                    >
                        What are you playing today, Alex?
                    </button>
                </div>

                <div class="composer-actions">

                    <button
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#postModal"
                    >
                        <span class="action-icon image-icon">▧</span>
                        Photo / Video
                    </button>

                    <button
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#postModal"
                    >
                        <span class="action-icon trophy-icon">♜</span>
                        Achievement
                    </button>

                    <button
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#postModal"
                    >
                        <span class="action-icon lfg-icon">⌁</span>
                        Find players
                    </button>

                    <button
                        class="more-action"
                        type="button"
                        data-bs-toggle="modal"
                        data-bs-target="#postModal"
                    >
                        •••
                    </button>

                </div>

            </section>

            <div class="feed-label">

                <h2>For you</h2>

                <span class="feed-line"></span>

                <button type="button">
                    Customize feed
                    <span>›</span>
                </button>

            </div>

            <?php foreach ($posts as $post): ?>

                <article class="post-card" data-post>

                    <div class="post-header">

                        <div class="author">

                            <span class="avatar avatar-<?= $post['tone'] ?>">
                                <?= $post['initials'] ?>
                            </span>

                            <div>
                                <strong><?= $post['author'] ?></strong>

                                <span class="handle">
                                    <?= $post['handle'] ?> · <?= $post['time'] ?>
                                </span>
                            </div>

                        </div>

                        <button
                            class="post-menu"
                            type="button"
                            aria-label="Post options"
                        >
                            •••
                        </button>

                    </div>

                    <div class="post-meta">

                        <span class="post-type type-<?= strtolower($post['type']) ?>">
                            <?= $post['type'] ?>
                        </span>

                        <span class="game-tag">
                            <?= $post['tag'] ?>
                        </span>

                        <span class="meta-separator">·</span>

                        <span>
                            <?= $post['game'] ?>
                        </span>

                    </div>

                    <p class="post-copy">
                        <?= $post['content'] ?>
                    </p>

                    <?php if ($post['image']): ?>

                        <img
                            class="post-image"
                            src="<?= $post['image'] ?>"
                            alt="A gaming setup with a monitor showing gameplay"
                        >

                    <?php endif; ?>

                    <div class="post-stats">

                        <span>
                            ♡ <?= $post['likes'] ?>
                        </span>

                        <span>
                            <?= $post['comments'] ?> comments
                        </span>

                        <span>
                            <?= $post['shares'] ?> shares
                        </span>

                    </div>

                    <div class="post-actions">

                        <button
                            type="button"
                            class="react-button"
                        >
                            <span>♡</span>
                            Like
                        </button>

                        <button type="button">
                            <span>◌</span>
                            Comment
                        </button>

                        <button type="button">
                            <span>↗</span>
                            Share
                        </button>

                        <button
                            type="button"
                            class="bookmark"
                            aria-label="Save post"
                        >
                            ⌑
                        </button>

                    </div>

                </article>

            <?php endforeach; ?>

            <button class="load-more" type="button">
                Load more posts
                <span>↓</span>
            </button>

        </section>

        <aside class="right-rail">

            <section class="rail-card now-playing">

                <div class="rail-heading">

                    <h2>Now playing</h2>

                    <span class="live-dot">LIVE</span>

                </div>

                <div class="playing-game">

                    <div class="game-art valorant-art">
                        V
                    </div>

                    <div>
                        <strong>Valorant</strong>
                        <span>Competitive · 02:18:44</span>
                    </div>

                    <button type="button">
                        •••
                    </button>

                </div>

                <div class="friend-row">

                    <span class="avatar avatar-blue small">
                        JD
                    </span>

                    <span>
                        <strong>Jordan Diaz</strong> is also playing
                    </span>

                    <span class="online"></span>

                </div>

            </section>

            <section class="rail-card suggested">

                <div class="rail-heading">

                    <h2>Suggested gamers</h2>

                    <button type="button">
                        See all
                    </button>

                </div>

                <div class="suggested-person">

                    <span class="avatar avatar-purple">
                        SL
                    </span>

                    <div>
                        <strong>Sam Lee</strong>
                        <span>Plays Apex Legends</span>
                    </div>

                    <button
                        type="button"
                        class="follow-button"
                    >
                        Follow
                    </button>

                </div>

                <div class="suggested-person">

                    <span class="avatar avatar-yellow">
                        NO
                    </span>

                    <div>
                        <strong>Nadia Ortiz</strong>
                        <span>Plays League of Legends</span>
                    </div>

                    <button
                        type="button"
                        class="follow-button"
                    >
                        Follow
                    </button>

                </div>

                <div class="suggested-person">

                    <span class="avatar avatar-green">
                        TW
                    </span>

                    <div>
                        <strong>Taylor Wong</strong>
                        <span>Plays Minecraft</span>
                    </div>

                    <button
                        type="button"
                        class="follow-button"
                    >
                        Follow
                    </button>

                </div>

            </section>

            <section class="rail-card trending">

                <div class="rail-heading">

                    <h2>Trending games</h2>

                    <span class="trend-icon">↗</span>

                </div>

                <div class="trend-item">

                    <span class="trend-number">
                        01
                    </span>

                    <div>
                        <strong>VALORANT</strong>
                        <span>2.4k posts today</span>
                    </div>

                    <b>↗</b>

                </div>

                <div class="trend-item">

                    <span class="trend-number">
                        02
                    </span>

                    <div>
                        <strong>MINECRAFT</strong>
                        <span>1.8k posts today</span>
                    </div>

                    <b>↗</b>

                </div>

                <div class="trend-item">

                    <span class="trend-number">
                        03
                    </span>

                    <div>
                        <strong>APEX LEGENDS</strong>
                        <span>1.2k posts today</span>
                    </div>

                    <b>↗</b>

                </div>

            </section>

        </aside>

    </main>

    <?php require __DIR__ . '/../includes/shared_footer.php'; ?>

</div>

<div
    class="modal fade"
    id="postModal"
    tabindex="-1"
    aria-labelledby="postModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-lg">

        <div class="modal-content post-modal">

            <div class="modal-header">

                <div>
                    <p class="eyebrow">SHARE WITH YOUR CIRCLE</p>

                    <h2 id="postModalLabel">
                        Create a post
                    </h2>
                </div>

                <button
                    type="button"
                    class="modal-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    ×
                </button>

            </div>

            <div class="modal-body">

                <div class="post-tabs" role="tablist">

                    <button
                        class="post-tab active"
                        type="button"
                        data-post-type="Thoughts"
                    >
                        ✦ Thoughts
                    </button>

                    <button
                        class="post-tab"
                        type="button"
                        data-post-type="LFG"
                    >
                        ⌁ LFG
                    </button>

                    <button
                        class="post-tab"
                        type="button"
                        data-post-type="Achievements"
                    >
                        ♜ Achievements
                    </button>

                    <button
                        class="post-tab"
                        type="button"
                        data-post-type="Question"
                    >
                        ? Question
                    </button>

                </div>

                <div class="modal-author">

                    <span class="avatar avatar-coral">
                        AK
                    </span>

                    <div>
                        <strong>Alex Kim</strong>
                        <span>Posting to your feed</span>
                    </div>

                </div>

                <label
                    class="form-label"
                    for="game-select"
                >
                    Game
                </label>

                <select
                    class="form-select custom-select"
                    id="game-select"
                >
                    <?php foreach ($games as $game): ?>

                        <option>
                            <?= $game ?>
                        </option>

                    <?php endforeach; ?>
                </select>

                <label
                    class="form-label"
                    for="post-content"
                >
                    What's on your mind?
                </label>

                <textarea
                    class="form-control post-textarea"
                    id="post-content"
                    placeholder="Share a win, find your squad, or start a conversation..."
                ></textarea>

                <div class="composer-tools">

                    <button type="button">
                        ☺ <span>Emoji</span>
                    </button>

                    <button type="button">
                        ✧ <span>Add feeling</span>
                    </button>

                    <label for="media-upload">
                        ▧ <span>Photo / video</span>
                    </label>

                    <input
                        id="media-upload"
                        type="file"
                        accept="image/*,video/*"
                        hidden
                    >

                </div>

                <div class="modal-settings">

                    <div>

                        <label
                            class="form-label"
                            for="visibility"
                        >
                            Visibility
                        </label>

                        <select
                            class="form-select custom-select"
                            id="visibility"
                        >
                            <option>Everyone</option>
                            <option>Friends</option>
                            <option>Followers</option>
                            <option>Only Me</option>
                        </select>

                    </div>

                    <div>

                        <label
                            class="form-label"
                            for="tag-gamer"
                        >
                            Tag a gamer
                        </label>

                        <input
                            class="form-control"
                            id="tag-gamer"
                            placeholder="Enter gamer ID"
                        >

                    </div>

                </div>

            </div>

            <div class="modal-footer">

                <span class="draft-status">
                    Your post will appear in your feed
                </span>

                <button
                    type="button"
                    class="publish-button"
                >
                    Publish post
                    <span>→</span>
                </button>

            </div>

        </div>

    </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-4">

    <div
        id="appToast"
        class="toast"
        role="alert"
    >
        <div class="toast-body">
            Coming soon. We are leveling this feature up.
        </div>
    </div>

</div>

<script src="../assets/js/shared/header.js"></script>
<script src="../assets/js/shared/sidebar.js"></script>
<script src="../assets/js/shared/footer.js"></script>
<script src="../assets/js/layout_functions.js"></script>

</body>

</html>