<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$errors = [];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
        $errors[] = 'Your form expired. Please try again.';
    } else {
        $identifier = trim((string) ($_POST['identifier'] ?? ''));

        if ($identifier === '') {
            $errors[] = 'Enter your email or username.';
        } else {
            try {
                $database = db();
                $statement = $database->prepare(
                    'SELECT u.id, u.username, u.email, p.display_name
                     FROM users u
                     LEFT JOIN user_profiles p ON p.user_id = u.id
                     WHERE LOWER(u.email) = :email OR LOWER(u.username) = :username
                     LIMIT 1'
                );
                $normalizedIdentifier = strtolower($identifier);
                $statement->execute([
                    'email' => $normalizedIdentifier,
                    'username' => $normalizedIdentifier,
                ]);
                $user = $statement->fetch();

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $database->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id')->execute([
                        'user_id' => $user['id'],
                    ]);
                    $tokenStatement = $database->prepare(
                        'INSERT INTO password_reset_tokens (token_hash, user_id, expires_at)
                         VALUES (:token_hash, :user_id, DATE_ADD(NOW(), INTERVAL 1 HOUR))'
                    );
                    $tokenStatement->execute([
                        'token_hash' => hash('sha256', $token),
                        'user_id' => $user['id'],
                    ]);
                    $resetUrl = absoluteAppUrl('reset/@' . rawurlencode((string) $user['username'])) . '?token=' . rawurlencode($token);
                    sendPasswordResetEmail(
                        (string) $user['email'],
                        (string) ($user['display_name'] ?: $user['username']),
                        $resetUrl
                    );
                }

                $sent = true;
            } catch (PDOException $exception) {
                error_log($exception->getMessage());
                $errors[] = 'We could not process your request. Please try again.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password | GamersHUB</title>
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
        <div class="intro-copy"><p class="eyebrow">ACCOUNT ACCESS</p><h1>Get back in the game.</h1><p>We will send a secure password reset link to the email on your account.</p></div>
        <p class="intro-footer">Your next session is waiting.</p>
    </section>
    <section class="auth-panel">
        <div class="auth-card">
            <p class="eyebrow">PASSWORD RESET</p>
            <h2>Forgot password?</h2>
            <p class="subheading">Enter your email or username and we will send a reset link.</p>
            <?php if ($errors): ?><div class="form-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(appUrl('forgot-password'), ENT_QUOTES, 'UTF-8') ?>" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                <label for="identifier">Email or username</label>
                <input id="identifier" name="identifier" type="text" value="<?= htmlspecialchars((string) ($_POST['identifier'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" autocomplete="username" required autofocus>
                <button class="primary-button" type="submit">Send password reset link <span aria-hidden="true">&#8594;</span></button>
            </form>
            <p class="switch-auth"><a href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">Back to log in</a></p>
        </div>
    </section>
</main>
<?php if ($sent): ?>
<div class="modal-backdrop" role="presentation">
    <section class="auth-modal" role="dialog" aria-modal="true" aria-labelledby="reset-sent-title">
        <p class="eyebrow">CHECK YOUR INBOX</p>
        <h2 id="reset-sent-title">Password reset link sent</h2>
        <p>If an account matches that information, you will receive a password reset link shortly.</p>
        <a class="primary-button" href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">Back to log in <span aria-hidden="true">&#8594;</span></a>
    </section>
</div>
<?php endif; ?>
</body>
</html>
