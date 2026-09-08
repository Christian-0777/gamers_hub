<?php

declare(strict_types=1);

require_once __DIR__ . '/config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH) ?: '/';
$basePath = appBasePath();
$route = trim(substr($requestPath, strlen($basePath)), '/');

$routes = [
    'home' => __DIR__ . '/authenticated_pages/home.php',
    'login' => __DIR__ . '/auth_page/login.php',
    'signup' => __DIR__ . '/auth_page/signup.php',
    'verify' => __DIR__ . '/auth_page/verify.php',
    'preferences' => __DIR__ . '/auth_page/preferences.php',
];

if ($route === '' && isset($_SESSION['user_id'], $_SESSION['auth_session_id'])) {
    header('Location: ' . appUrl('home'));
    exit;
}

if (isset($routes[$route])) {
    require $routes[$route];
    exit;
}

if ($route !== '') {
    http_response_code(404);
    $route = '';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GamersHUB | Find your people</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap"
        rel="stylesheet"
    >
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/landing.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<main class="landing-shell">
    <nav class="landing-nav" aria-label="Primary navigation">
            <a class="brand" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>" aria-label="GamersHUB home">
            <span class="brand-mark">G</span>
            <span>Gamers<span class="brand-accent">HUB</span></span>
        </a>

        <div class="nav-actions">
            <a class="text-link" href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">Log in</a>
            <a class="nav-button" href="<?= htmlspecialchars(appUrl('signup'), ENT_QUOTES, 'UTF-8') ?>">
                Sign up
                <span aria-hidden="true">&#8594;</span>
            </a>
        </div>
    </nav>
    <section class="landing-hero">
        <div class="hero-copy">
            <p class="eyebrow">THE SOCIAL HUB FOR PLAYERS</p>

            <h1 id="hero-title">
                Your people are<br>
                <em>already online.</em>
            </h1>

            <p class="hero-text">
                Find teammates who get it, share the moments worth replaying, and make every session feel like a good one.
            </p>

            <div class="hero-actions">
                <a class="primary-button" href="<?= htmlspecialchars(appUrl('signup'), ENT_QUOTES, 'UTF-8') ?>">
                    Join the community
                    <span aria-hidden="true">&#8594;</span>
                </a>

                <a class="secondary-link" href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">
                    I already have an account
                </a>
            </div>
        </div>
        <div class="hero-scene" aria-label="Gaming community highlights">
            <div class="scene-orbit orbit-one" aria-hidden="true"></div>
            <div class="scene-orbit orbit-two" aria-hidden="true"></div>

            <article class="scene-card main-card">
                <p class="card-kicker">NOW PLAYING</p>
                <div class="game-symbol" aria-hidden="true">V</div>
                <h2>Valorant</h2>
                <p>Competitive · 02:18:44</p>

                <div class="player-stack" aria-label="Four squad members online">
                    <span>MC</span>
                    <span>JD</span>
                    <span>+4</span>
                    <b>Squad online</b>
                </div>
            </article>

            <article class="scene-card quote-card">
                <span class="quote-mark" aria-hidden="true">&#8220;</span>
                <p>Finally found a crew that plays to win and still knows how to laugh.</p>
                <span class="quote-author">MAYA CHEN · ASCENDANT</span>
            </article>

            <div class="scene-label label-top">
                08
                <span>LEVEL UP</span>
            </div>

            <div class="scene-label label-bottom">
                2.4k
                <span>PLAYERS ONLINE</span>
            </div>
        </div>
    </section>

    <section class="landing-strip" aria-label="GamersHUB features">
        <span>BUILT FOR THE WAY YOU PLAY</span>
        <span>01 / FIND YOUR CREW</span>
        <span>02 / SHARE THE WINS</span>
        <span>03 / KEEP PLAYING</span>
    </section>
</main>
</body>
</html>
