<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    header('Location: ' . appUrl('login'));
    exit;
}

$database = db();
$sessionStatement = $database->prepare(
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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$gamerTypes = [
    'casual' => '🎮 Casual',
    'competitive' => '🏆 Competitive',
    'multiplayer' => '👥 Multiplayer',
    'co-op' => '🧑‍🤝‍🧑 Co-op',
    'ranked' => '🎯 Ranked',
    'single-player' => '🕹️ Single-player',
    'everything' => '🔥 I play a bit of everything',
];
$platforms = ['PC', 'PlayStation', 'Xbox', 'Nintendo Switch', 'Mobile', 'Steam Deck', 'Other'];
$goals = [
    'friends' => 'Find gaming friends',
    'lfg' => 'Find teammates / LFG',
    'communities' => 'Join gaming communities',
    'discover' => 'Discover new games',
    'share' => 'Share gaming experiences',
    'discuss' => 'Discuss games',
    'all' => 'All of the above',
];
$contentTypes = [
    'discussions' => 'Game discussions',
    'tips' => 'Gaming tips',
    'achievements' => 'Achievements',
    'lfg' => 'LFG posts',
    'reviews' => 'Game reviews',
    'news' => 'Gaming news',
    'memes' => 'Memes / casual posts',
    'everything' => 'Everything',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Your form expired. Please try again.';
    } elseif (isset($_POST['skip_preferences'])) {
        header('Location: ' . appUrl('home'));
        exit;
    } else {
        $selectedTypes = array_values(array_intersect(array_keys($gamerTypes), (array) ($_POST['gamer_types'] ?? [])));
        $selectedPlatforms = array_values(array_intersect($platforms, (array) ($_POST['platforms'] ?? [])));
        $selectedGoals = array_values(array_intersect(array_keys($goals), (array) ($_POST['goals'] ?? [])));
        $selectedContent = array_values(array_intersect(array_keys($contentTypes), (array) ($_POST['content_types'] ?? [])));
        $selectedGameIds = array_values(array_unique(array_filter(array_map('intval', (array) ($_POST['games'] ?? [])), static fn (int $id): bool => $id > 0)));

        try {
            $database->beginTransaction();
            $deleteStatements = [
                'DELETE FROM user_gamer_types WHERE user_id = :user_id',
                'DELETE FROM user_platforms WHERE user_id = :user_id',
                'DELETE FROM user_goals WHERE user_id = :user_id',
                'DELETE FROM user_content_preferences WHERE user_id = :user_id',
                'DELETE FROM user_games WHERE user_id = :user_id',
            ];
            foreach ($deleteStatements as $sql) {
                $database->prepare($sql)->execute(['user_id' => $_SESSION['user_id']]);
            }

            $insertPreference = static function (PDO $database, string $table, string $column, int $userId, array $values): void {
                $statement = $database->prepare("INSERT INTO {$table} (user_id, {$column}) VALUES (:user_id, :value)");
                foreach ($values as $value) {
                    $statement->execute(['user_id' => $userId, 'value' => $value]);
                }
            };
            $userId = (int) $_SESSION['user_id'];
            $insertPreference($database, 'user_gamer_types', 'gamer_type', $userId, $selectedTypes);
            $insertPreference($database, 'user_platforms', 'platform', $userId, $selectedPlatforms);
            $insertPreference($database, 'user_goals', 'goal', $userId, $selectedGoals);
            $insertPreference($database, 'user_content_preferences', 'content_type', $userId, $selectedContent);

            if ($selectedGameIds) {
                $placeholders = implode(',', array_fill(0, count($selectedGameIds), '?'));
                $gameStatement = $database->prepare("SELECT id FROM games WHERE status = 'active' AND id IN ({$placeholders})");
                $gameStatement->execute($selectedGameIds);
                $validGameIds = $gameStatement->fetchAll(PDO::FETCH_COLUMN);
                $userGameStatement = $database->prepare('INSERT INTO user_games (user_id, game_id) VALUES (:user_id, :game_id)');
                foreach ($validGameIds as $gameId) {
                    $userGameStatement->execute(['user_id' => $userId, 'game_id' => $gameId]);
                }
            }
            $database->commit();
            header('Location: ' . appUrl('home'));
            exit;
        } catch (PDOException $exception) {
            if ($database->inTransaction()) {
                $database->rollBack();
            }
            error_log($exception->getMessage());
            $errors[] = 'We could not save your preferences. Please try again.';
        }
    }
}

$games = $database->query("SELECT id, name FROM games WHERE status = 'active' ORDER BY name")->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose your preferences | GamersHUB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/auth/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<main class="preferences-shell">
    <section class="preferences-header">
        <a class="brand" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>"><span>G</span> GamersHUB</a>
        <div><p class="eyebrow">STEP 3 OF 3</p><h1>Make your hub feel like yours.</h1><p>These answers shape your feed and help us find your people. You can skip this and update it later.</p></div>
        <a class="skip-link" href="<?= htmlspecialchars(appUrl('home'), ENT_QUOTES, 'UTF-8') ?>">Skip for now</a>
    </section>
    <section class="preferences-panel">
        <div class="preference-card">
            <?php if ($errors): ?><div class="form-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(appUrl('preferences'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <fieldset><legend>1. What type of gamer are you?</legend><p class="field-help">Choose one or more</p><div class="choice-grid choice-grid-wide"><?php foreach ($gamerTypes as $value => $label): ?><label class="choice"><input type="checkbox" name="gamer_types[]" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <fieldset><legend>2. Which platforms do you play on?</legend><div class="choice-grid"><?php foreach ($platforms as $platform): ?><label class="choice"><input type="checkbox" name="platforms[]" value="<?= htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($platform, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <fieldset><legend>3. What games do you play?</legend><label class="search-field" for="game-search">🔍 <input id="game-search" type="search" placeholder="Search games..." autocomplete="off"></label><div class="choice-grid choice-grid-wide" id="game-list"><?php foreach ($games as $game): ?><label class="choice game-choice" data-game-name="<?= htmlspecialchars(strtolower($game['name']), ENT_QUOTES, 'UTF-8') ?>"><input type="checkbox" name="games[]" value="<?= (int) $game['id'] ?>"><span><?= htmlspecialchars($game['name'], ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div><p class="field-help">You can add games later.</p></fieldset>
                <fieldset><legend>4. What are you looking for on GamersHUB?</legend><div class="choice-grid choice-grid-wide"><?php foreach ($goals as $value => $label): ?><label class="choice"><input type="checkbox" name="goals[]" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <fieldset><legend>5. What kind of content do you want to see?</legend><div class="choice-grid choice-grid-wide"><?php foreach ($contentTypes as $value => $label): ?><label class="choice"><input type="checkbox" name="content_types[]" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <div class="preference-actions"><button class="secondary-button" type="submit" name="skip_preferences" value="1">Add preferences later</button><button class="primary-button" type="submit">Save preferences <span aria-hidden="true">&#8594;</span></button></div>
            </form>
        </div>
    </section>
</main>
<script>
const gameSearch = document.querySelector('#game-search');
gameSearch?.addEventListener('input', () => {
    const query = gameSearch.value.trim().toLowerCase();
    document.querySelectorAll('.game-choice').forEach((game) => {
        game.hidden = query !== '' && !game.dataset.gameName.includes(query);
    });
});
</script>
</body>
</html>
