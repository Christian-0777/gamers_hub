<?php
require_once __DIR__ . '/auth.php';
$result = handleAuthRequest('login');
$errors = $result['errors'];
$notice = $result['notice'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in | GamersHUB</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/auth/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<main class="auth-shell">
    <section class="auth-intro">
        <a class="brand" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>"><img src="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt=""> GamersHUB</a>
        <div class="intro-copy">
            <p class="eyebrow">YOUR CREW IS ONLINE</p>
            <h1>Pick up where the good games left off.</h1>
            <p>Find your people, share the highlights, and keep every session moving.</p>
        </div>
        <p class="intro-footer">A home for every kind of player.</p>
    </section>
    <section class="auth-panel">
        <div class="auth-card">
            <p class="eyebrow">WELCOME BACK</p>
            <h2>Log in</h2>
            <p class="subheading">Your squad has been waiting.</p>
            <?php if ($errors): ?><div class="form-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="form-success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="identifier">Username or email</label>
                <input id="identifier" name="identifier" type="text" value="<?= authOld('identifier') ?>" autocomplete="username" required>
                <div class="label-row"><label for="password">Password</label><a href="<?= htmlspecialchars(appUrl('forgot-password'), ENT_QUOTES, 'UTF-8') ?>">Forgot password?</a></div>
                <div class="password-field"><input id="password" name="password" type="password" autocomplete="current-password" required><button type="button" data-toggle-password="password" aria-label="Show password">Show</button></div>
                <button class="primary-button" type="submit">Log in <span aria-hidden="true">&#8594;</span></button>
            </form>
            <p class="switch-auth">New to GamersHUB? <a href="<?= htmlspecialchars(appUrl('signup'), ENT_QUOTES, 'UTF-8') ?>">Create an account</a></p>
        </div>
    </section>
</main>
<script src="<?= htmlspecialchars(appUrl('assets/js/auth/auth.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
