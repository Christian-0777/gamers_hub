<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    header('Location: ' . appUrl('login'));
    exit;
}

$database = db();
$session = $database->prepare('SELECT user_id FROM sessions WHERE id = :id AND user_id = :user_id AND expires_at > NOW() LIMIT 1');
$session->execute(['id' => $_SESSION['auth_session_id'], 'user_id' => $_SESSION['user_id']]);
if (!$session->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['auth_session_id']);
    header('Location: ' . appUrl('login'));
    exit;
}

$userStatement = $database->prepare(
    'SELECT u.username, p.display_name, p.avatar_url
     FROM users u INNER JOIN user_profiles p ON p.user_id = u.id
     WHERE u.id = :user_id LIMIT 1'
);
$userStatement->execute(['user_id' => $_SESSION['user_id']]);
$user = $userStatement->fetch() ?: ['username' => 'gamer', 'display_name' => 'Gamer', 'avatar_url' => null];
$name = (string) $user['display_name'];
$avatar = $user['avatar_url'] ?: appUrl('assets/icons/profile.png');
$profileUrl = appUrl('@' . $user['username']);
$dashboardLayout = true;
$dashboardActivePage = 'mygames';

$gameStatement = $database->prepare(
        'SELECT g.id, g.name,
            (SELECT GROUP_CONCAT(cc.name ORDER BY cc.name SEPARATOR \', \')
             FROM game_companies gc
             INNER JOIN company_catalog cc ON cc.id = gc.company_id
             WHERE gc.game_id = g.id AND gc.role = \'developer\') AS developer,
            g.cover_url, g.icon_url,
            COUNT(gu.id) AS update_count,
            MAX(gu.published_at) AS latest_update_at
     FROM user_games ug
     INNER JOIN game_catalog g ON g.id = ug.game_id
     LEFT JOIN game_updates gu ON gu.game_id = g.id
     WHERE ug.user_id = :user_id AND g.is_active = 1
    GROUP BY g.id, g.name, g.cover_url, g.icon_url
     ORDER BY latest_update_at DESC, g.name ASC'
);
$gameStatement->execute(['user_id' => $_SESSION['user_id']]);
$games = $gameStatement->fetchAll();

$updateStatement = $database->prepare(
    'SELECT gu.id, gu.game_id, gu.title, gu.description, gu.source_url, gu.update_type, gu.published_at, g.name AS game_name
     FROM game_updates gu INNER JOIN game_catalog g ON g.id = gu.game_id
     INNER JOIN user_games ug ON ug.game_id = gu.game_id AND ug.user_id = :user_id
     ORDER BY COALESCE(gu.published_at, gu.created_at) DESC
     LIMIT 40'
);
$updateStatement->execute(['user_id' => $_SESSION['user_id']]);
$updates = $updateStatement->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Games | GamersHUB</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/layout.css?v=2'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/mygames.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard-page">
<div class="dashboard-backdrop" id="dashboardBackdrop"></div>
<?php require __DIR__ . '/../includes/left_sidebar.php'; ?>
<div class="dashboard-main" id="dashboardMain">
    <?php require __DIR__ . '/../includes/header.php'; ?>
    <main class="mygames-page">
        <header class="mygames-heading">
            <div><p class="mygames-kicker">YOUR LIBRARY</p><h1>My games</h1><p>Updates from the games you actually play, collected in one place.</p></div>
            <a class="mygames-settings-link" href="<?= htmlspecialchars(appUrl('settings?tab=gamer-preference'), ENT_QUOTES, 'UTF-8') ?>"><span class="material-symbols-rounded" aria-hidden="true">tune</span> Manage preferences</a>
        </header>
        <section class="mygames-library" aria-labelledby="library-title">
            <div class="section-heading"><div><p class="mygames-kicker">FOLLOWING</p><h2 id="library-title">Games in your library</h2></div><span><?= count($games) ?> selected</span></div>
            <div class="game-rail">
                <?php if (!$games): ?><div class="mygames-empty">Select games in Gamer's Preference to start receiving updates.</div><?php endif; ?>
                <?php foreach ($games as $game): ?><article class="game-tile"><div class="game-tile-art" style="background-image:url('<?= htmlspecialchars((string) ($game['cover_url'] ?: $game['icon_url'] ?: appUrl('assets/icons/logo.png')), ENT_QUOTES, 'UTF-8') ?>')"></div><div class="game-tile-copy"><strong><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></strong><span><?= htmlspecialchars((string) ($game['developer'] ?: 'Unknown developer'), ENT_QUOTES, 'UTF-8') ?></span><small><?= (int) $game['update_count'] ?> updates</small></div></article><?php endforeach; ?>
            </div>
        </section>
        <section class="mygames-updates" aria-labelledby="updates-title">
            <div class="section-heading"><div><p class="mygames-kicker">LATEST INTELLIGENCE</p><h2 id="updates-title">Game updates</h2></div><span class="source-note"><span class="status-dot"></span> SCS Software + Valorant feeds</span></div>
            <div class="update-list">
                <?php if (!$updates): ?><div class="mygames-empty">No updates have been collected for your games yet.</div><?php endif; ?>
                <?php foreach ($updates as $update): ?><article class="update-item"><div class="update-marker"><span class="material-symbols-rounded" aria-hidden="true">campaign</span></div><div class="update-copy"><div class="update-meta"><span><?= htmlspecialchars($update['game_name'], ENT_QUOTES, 'UTF-8') ?></span><time datetime="<?= htmlspecialchars((string) $update['published_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($update['published_at'] ? date('M j, Y', strtotime($update['published_at'])) : 'Recently', ENT_QUOTES, 'UTF-8') ?></time></div><h3><?= htmlspecialchars($update['title'], ENT_QUOTES, 'UTF-8') ?></h3><?php if (!empty($update['description'])): ?><p><?= htmlspecialchars(trim(strip_tags($update['description'])), ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><a href="<?= htmlspecialchars($update['source_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Read source <span aria-hidden="true">&#8599;</span></a></div></article><?php endforeach; ?>
            </div>
        </section>
    </main>
    <?php require __DIR__ . '/../includes/shared_footer.php'; ?>
</div>
<script src="<?= htmlspecialchars(appUrl('assets/js/shared/layout_functions.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
