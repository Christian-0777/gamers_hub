<?php
require_once __DIR__ . '/auth.php';
$result = handleAuthRequest('signup');
$errors = $result['errors'];
$notice = $result['notice'];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign up | GamersHUB</title>
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
            <p class="eyebrow">MAKE YOUR NEXT MOVE</p>
            <h1>Bring your game face. Leave the gatekeeping.</h1>
            <p>Build a profile around the games you actually play and meet a better kind of teammate.</p>
        </div>
        <p class="intro-footer">More players. Better sessions.</p>
    </section>
    <section class="auth-panel">
        <div class="auth-card">
            <p class="eyebrow">JOIN THE HUB</p>
            <h2>Create account</h2>
            <p class="subheading">Set up your gamer identity and join your new crew.</p>
            <?php if ($errors): ?><div class="form-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <?php if ($notice): ?><div class="form-success" role="status"><?= htmlspecialchars($notice, ENT_QUOTES, 'UTF-8') ?> <a href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">Log in</a></div><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(appUrl('signup'), ENT_QUOTES, 'UTF-8') ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="display_name">Display name</label>
                <input id="display_name" name="display_name" type="text" value="<?= authOld('display_name') ?>" maxlength="50" autocomplete="nickname" required>
                <label for="username">Username</label>
                <input id="username" name="username" type="text" value="<?= authOld('username') ?>" maxlength="30" autocomplete="username" required>
                <label for="email">Email address</label>
                <input id="email" name="email" type="email" value="<?= authOld('email') ?>" maxlength="255" autocomplete="email" required>
                <label for="password">Password</label>
                <div class="password-field"><input id="password" name="password" type="password" autocomplete="new-password" required><button type="button" data-toggle-password="password" aria-label="Show password">Show</button></div>
                <label for="password_confirmation">Confirm password</label>
                <div class="password-field"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><button type="button" data-toggle-password="password_confirmation" aria-label="Show password">Show</button></div>
                <label class="check-row" for="terms"><input id="terms" name="terms" type="checkbox" value="1" <?= isset($_POST['terms']) ? 'checked' : '' ?> required><span>I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.</span></label>
                <button class="primary-button" id="create-account-button" type="submit" disabled>Create account <span aria-hidden="true">&#8594;</span></button>
            </form>
            <div class="social-signup" aria-label="Social sign up options">
                <button type="button" class="secondary-button" data-coming-soon="Google">Sign up with Google</button>
                <button type="button" class="secondary-button" data-coming-soon="Discord">Sign up with Discord</button>
                <button type="button" class="secondary-button" data-coming-soon="Steam">Sign up with Steam</button>
            </div>
            <p class="switch-auth">Already have an account? <a href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">Log in</a></p>
        </div>
    </section>
</main>
<script src="<?= htmlspecialchars(appUrl('assets/js/auth/auth.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
