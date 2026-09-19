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
$userId = (int) $_SESSION['user_id'];

$selectedGameStatement = $database->prepare(
    'SELECT game_id FROM user_games WHERE user_id = :user_id ORDER BY game_id'
);
$selectedGameStatement->execute(['user_id' => $userId]);
$selectedGameIds = array_map('intval', $selectedGameStatement->fetchAll(PDO::FETCH_COLUMN));
$selectedGameRows = [];
if ($selectedGameIds) {
    $placeholders = implode(',', array_fill(0, count($selectedGameIds), '?'));
    $selectedGameCatalogStatement = $database->prepare(
        "SELECT g.id, g.name,
            (SELECT cc.name FROM game_companies gc INNER JOIN company_catalog cc ON cc.id = gc.company_id WHERE gc.game_id = g.id AND gc.role = 'developer' ORDER BY cc.name LIMIT 1) AS developer
         FROM game_catalog g WHERE g.is_active = 1 AND g.id IN ({$placeholders}) ORDER BY g.name"
    );
    $selectedGameCatalogStatement->execute($selectedGameIds);
    $selectedGameRows = $selectedGameCatalogStatement->fetchAll();
}

$selectedDeveloperStatement = $database->prepare(
    'SELECT cc.name
     FROM user_companies uc
     INNER JOIN company_catalog cc ON cc.id = uc.company_id
     WHERE uc.user_id = :user_id AND uc.role = \'developer\'
     ORDER BY cc.name'
);
$selectedDeveloperStatement->execute(['user_id' => $userId]);
$selectedDevelopers = $selectedDeveloperStatement->fetchAll(PDO::FETCH_COLUMN);

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
        $selectedDevelopers = array_values(array_unique(array_filter(array_map(
            static fn ($developer): string => trim((string) $developer),
            (array) ($_POST['developers'] ?? [])
        ), static fn (string $developer): bool => $developer !== '')));

        try {
            $database->beginTransaction();
            $deleteStatements = [
                'DELETE FROM user_gamer_types WHERE user_id = :user_id',
                'DELETE FROM user_platforms WHERE user_id = :user_id',
                'DELETE FROM user_goals WHERE user_id = :user_id',
                'DELETE FROM user_content_preferences WHERE user_id = :user_id',
                'DELETE FROM user_games WHERE user_id = :user_id',
                'DELETE FROM user_companies WHERE user_id = :user_id AND role = \'developer\'',
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
                $gameStatement = $database->prepare("SELECT id FROM game_catalog WHERE is_active = 1 AND id IN ({$placeholders})");
                $gameStatement->execute($selectedGameIds);
                $validGameIds = $gameStatement->fetchAll(PDO::FETCH_COLUMN);
                $userGameStatement = $database->prepare('INSERT INTO user_games (user_id, game_id) VALUES (:user_id, :game_id)');
                foreach ($validGameIds as $gameId) {
                    $userGameStatement->execute(['user_id' => $userId, 'game_id' => $gameId]);
                }
            }

            if ($selectedDevelopers) {
                $placeholders = implode(',', array_fill(0, count($selectedDevelopers), '?'));
                $developerStatement = $database->prepare(
                    "SELECT DISTINCT cc.name
                     FROM company_catalog cc
                     INNER JOIN game_companies gc ON gc.company_id = cc.id AND gc.role = 'developer'
                     INNER JOIN game_catalog g ON g.id = gc.game_id AND g.is_active = 1
                     WHERE cc.name IN ({$placeholders})"
                );
                $developerStatement->execute($selectedDevelopers);
                $validDevelopers = $developerStatement->fetchAll(PDO::FETCH_COLUMN);
                $userDeveloperStatement = $database->prepare(
                    'INSERT INTO user_companies (user_id, company_id, role)
                     SELECT :user_id, id, \'developer\' FROM company_catalog WHERE name = :name'
                );
                foreach ($validDevelopers as $developer) {
                    $userDeveloperStatement->execute(['user_id' => $userId, 'name' => $developer]);
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
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose your preferences | GamersHUB</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/auth/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<main class="preferences-shell">
    <section class="preferences-header">
                <a class="brand" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>"><img src="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt=""> GamersHUB</a>
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
                <fieldset>
                    <legend>3. What games do you play?</legend>
                    <label class="search-field" for="game-search">&#128269; <input id="game-search" type="search" placeholder="Search by game or developer..." autocomplete="off"></label>
                    <div class="catalog-table-wrap">
                        <table class="catalog-table" aria-live="polite">
                            <thead><tr><th>Game</th><th>Developer</th><th><span class="sr-only">Action</span></th></tr></thead>
                            <tbody id="game-results"></tbody>
                        </table>
                    </div>
                    <div class="selected-items" id="selected-games">
                        <h3>Added games</h3>
                        <div class="selected-table-wrap"><table class="catalog-table"><tbody></tbody></table></div>
                    </div>
                    <?php foreach ($selectedGameIds as $gameId): ?><input type="hidden" name="games[]" value="<?= (int) $gameId ?>" data-selected-game-input="<?= (int) $gameId ?>"><?php endforeach; ?>
                    <p class="field-help">Start typing to search every game or developer. Add the games you play.</p>
                </fieldset>
                <fieldset>
                    <legend>What developers do you like?</legend>
                    <label class="search-field" for="developer-search">&#128269; <input id="developer-search" type="search" placeholder="Search developers..." autocomplete="off"></label>
                    <div class="catalog-table-wrap">
                        <table class="catalog-table" aria-live="polite">
                            <thead><tr><th>Developer</th><th><span class="sr-only">Action</span></th></tr></thead>
                            <tbody id="developer-results"></tbody>
                        </table>
                    </div>
                    <div class="selected-items" id="selected-developers">
                        <h3>Added developers</h3>
                        <div class="selected-table-wrap"><table class="catalog-table"><tbody></tbody></table></div>
                    </div>
                    <?php foreach ($selectedDevelopers as $developer): ?><input type="hidden" name="developers[]" value="<?= htmlspecialchars((string) $developer, ENT_QUOTES, 'UTF-8') ?>" data-selected-developer-input="<?= htmlspecialchars((string) $developer, ENT_QUOTES, 'UTF-8') ?>"><?php endforeach; ?>
                    <p class="field-help">Choose the studios and publishers whose games you follow.</p>
                </fieldset>
                <fieldset><legend>4. What are you looking for on GamersHUB?</legend><div class="choice-grid choice-grid-wide"><?php foreach ($goals as $value => $label): ?><label class="choice"><input type="checkbox" name="goals[]" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <fieldset><legend>5. What kind of content do you want to see?</legend><div class="choice-grid choice-grid-wide"><?php foreach ($contentTypes as $value => $label): ?><label class="choice"><input type="checkbox" name="content_types[]" value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"><span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
                <div class="preference-actions"><button class="secondary-button" type="submit" name="skip_preferences" value="1">Add preferences later</button><button class="primary-button" type="submit">Save preferences <span aria-hidden="true">&#8594;</span></button></div>
            </form>
        </div>
    </section>
</main>
<script>
const catalogUrl = <?= json_encode(appUrl('api/list.php'), JSON_UNESCAPED_SLASHES) ?>;
const selectedGames = new Map(Object.entries(<?= json_encode(array_column($selectedGameRows, null, 'id'), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>));
const selectedDevelopers = new Map(<?= json_encode(array_values($selectedDevelopers), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>.map((developer) => [developer, developer]));
const searchControllers = {};

const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
}[character]));

const renderSelectedGames = () => {
    const body = document.querySelector('#selected-games tbody');
    body.innerHTML = selectedGames.size ? [...selectedGames.values()].map((game) => `
        <tr><td>${escapeHtml(game.name)}</td><td>${escapeHtml(game.developer || 'Unknown')}</td>
        <td><button type="button" data-remove-game="${escapeHtml(game.id)}">Remove</button></td></tr>`).join('')
        : '<tr><td class="catalog-empty" colspan="3">No games added yet.</td></tr>';
    document.querySelectorAll('input[data-selected-game-input]').forEach((input) => input.remove());
    selectedGames.forEach((game) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'games[]'; input.value = game.id; input.dataset.selectedGameInput = game.id;
        document.querySelector('form').append(input);
    });
};

const renderSelectedDevelopers = () => {
    const body = document.querySelector('#selected-developers tbody');
    body.innerHTML = selectedDevelopers.size ? [...selectedDevelopers.values()].map((developer) => `
        <tr><td>${escapeHtml(developer)}</td><td><button type="button" data-remove-developer="${escapeHtml(developer)}">Remove</button></td></tr>`).join('')
        : '<tr><td class="catalog-empty" colspan="2">No developers added yet.</td></tr>';
    document.querySelectorAll('input[data-selected-developer-input]').forEach((input) => input.remove());
    selectedDevelopers.forEach((developer) => {
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'developers[]'; input.value = developer; input.dataset.selectedDeveloperInput = developer;
        document.querySelector('form').append(input);
    });
};

const loadResults = async (type, query, target) => {
    target.innerHTML = '<tr><td class="catalog-empty" colspan="3">Searching...</td></tr>';
    searchControllers[type]?.abort();
    searchControllers[type] = new AbortController();
    const response = await fetch(`${catalogUrl}?type=${encodeURIComponent(type)}&q=${encodeURIComponent(query)}`, { headers: { Accept: 'application/json' }, signal: searchControllers[type].signal });
    const payload = await response.json();
    if (!payload.success) throw new Error(payload.error || 'Search failed');
    if (type === 'games') {
        target.innerHTML = payload.results.length ? payload.results.map((game) => `
            <tr><td>${escapeHtml(game.name)}</td><td>${escapeHtml(game.developer || 'Unknown')}</td>
            <td><button type="button" data-add-game="${escapeHtml(game.id)}" ${selectedGames.has(String(game.id)) ? 'disabled' : ''}>${selectedGames.has(String(game.id)) ? 'Added' : 'Add'}</button></td></tr>`).join('')
            : '<tr><td class="catalog-empty" colspan="3">No matching games.</td></tr>';
    } else {
        target.innerHTML = payload.results.length ? payload.results.map((item) => `
            <tr><td>${escapeHtml(item.name)}</td><td><button type="button" data-add-developer="${escapeHtml(item.name)}" ${selectedDevelopers.has(item.name) ? 'disabled' : ''}>${selectedDevelopers.has(item.name) ? 'Added' : 'Add'}</button></td></tr>`).join('')
            : '<tr><td class="catalog-empty" colspan="2">No matching developers.</td></tr>';
    }
};

const refreshResults = (type, query, target) => loadResults(type, query, target).catch((error) => {
    if (error.name === 'AbortError') return;
    target.innerHTML = '<tr><td class="catalog-empty" colspan="3">Unable to load results.</td></tr>';
});

document.querySelector('#game-search')?.addEventListener('input', (event) => refreshResults('games', event.target.value.trim(), document.querySelector('#game-results')));
document.querySelector('#developer-search')?.addEventListener('input', (event) => refreshResults('developers', event.target.value.trim(), document.querySelector('#developer-results')));
document.addEventListener('click', (event) => {
    const addGame = event.target.closest('[data-add-game]');
    const removeGame = event.target.closest('[data-remove-game]');
    const addDeveloper = event.target.closest('[data-add-developer]');
    const removeDeveloper = event.target.closest('[data-remove-developer]');
    if (addGame) {
        const row = addGame.closest('tr');
        selectedGames.set(addGame.dataset.addGame, { id: addGame.dataset.addGame, name: row.cells[0].textContent, developer: row.cells[1].textContent });
        renderSelectedGames(); refreshResults('games', document.querySelector('#game-search').value.trim(), document.querySelector('#game-results'));
    }
    if (removeGame) { selectedGames.delete(removeGame.dataset.removeGame); renderSelectedGames(); refreshResults('games', document.querySelector('#game-search').value.trim(), document.querySelector('#game-results')); }
    if (addDeveloper) { selectedDevelopers.set(addDeveloper.dataset.addDeveloper, addDeveloper.dataset.addDeveloper); renderSelectedDevelopers(); refreshResults('developers', document.querySelector('#developer-search').value.trim(), document.querySelector('#developer-results')); }
    if (removeDeveloper) { selectedDevelopers.delete(removeDeveloper.dataset.removeDeveloper); renderSelectedDevelopers(); refreshResults('developers', document.querySelector('#developer-search').value.trim(), document.querySelector('#developer-results')); }
});

renderSelectedGames();
renderSelectedDevelopers();
refreshResults('games', '', document.querySelector('#game-results'));
refreshResults('developers', '', document.querySelector('#developer-results'));
</script>
</body>
</html>
