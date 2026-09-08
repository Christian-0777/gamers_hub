<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$errors = [];
$email = (string) ($_SESSION['pending_verification_email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim((string) ($_POST['verification_code'] ?? ''));
    if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = 'Your form expired. Please try again.';
    } elseif (!preg_match('/^\d{7}$/', $code)) {
        $errors[] = 'Enter the seven-digit code from your email.';
    } elseif ($email === '') {
        $errors[] = 'Your verification session has expired. Please sign up again.';
    } else {
        $database = null;
        try {
            $database = db();
            $database->beginTransaction();
            $statement = $database->prepare(
                'SELECT u.id, u.email, p.display_name
                 FROM email_verification_tokens t
                 JOIN users u ON u.id = t.user_id
                 JOIN user_profiles p ON p.user_id = u.id
                 WHERE t.token = :token AND u.email = :email AND t.expires_at > NOW()
                 LIMIT 1'
            );
            $statement->execute(['token' => $code, 'email' => $email]);
            $verification = $statement->fetch();
            if (!$verification) {
                $database->rollBack();
                $errors[] = 'That code is incorrect or has expired.';
            } else {
                $database->prepare('UPDATE users SET email_verified_at = NOW() WHERE id = :id')->execute(['id' => $verification['id']]);
                $database->prepare('DELETE FROM email_verification_tokens WHERE user_id = :id')->execute(['id' => $verification['id']]);
                session_regenerate_id(true);
                $sessionId = bin2hex(random_bytes(32));
                $sessionStatement = $database->prepare(
                    'INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (:id, :user_id, :ip, :agent, :expires_at)'
                );
                $sessionStatement->execute([
                    'id' => $sessionId,
                    'user_id' => $verification['id'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'expires_at' => (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s'),
                ]);
                $database->commit();
                unset($_SESSION['pending_verification_email']);
                $_SESSION['user_id'] = (int) $verification['id'];
                $_SESSION['auth_session_id'] = $sessionId;
                sendAccountCreatedEmail($email, $verification['display_name']);
                header('Location: ' . appUrl('preferences'));
                exit;
            }
        } catch (PDOException $exception) {
            if ($database instanceof PDO && $database->inTransaction()) {
                $database->rollBack();
            }
            error_log($exception->getMessage());
            $errors[] = 'We could not verify your account. Please try again.';
        }
    }
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify your account | GamersHUB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/auth/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<main class="auth-shell">
    <section class="auth-intro">
        <a class="brand" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>"><span>G</span> GamersHUB</a>
        <div class="intro-copy"><p class="eyebrow">ACCOUNT CHECK</p><h1>One last check before you enter the hub.</h1><p>Use the seven-digit code we sent to your email address.</p></div>
        <p class="intro-footer">Your crew is almost ready.</p>
    </section>
    <section class="auth-panel">
        <div class="auth-card">
            <p class="eyebrow">STEP 2 OF 3</p>
            <h2>Verify your email</h2>
            <p class="subheading">Enter the code sent to <?= htmlspecialchars($email ?: 'your email address', ENT_QUOTES, 'UTF-8') ?>.</p>
            <?php if ($errors): ?><div class="form-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
            <form method="post" action="<?= htmlspecialchars(appUrl('verify'), ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="verification_code">7-digit code</label>
                <input id="verification_code" name="verification_code" type="text" inputmode="numeric" pattern="[0-9]{7}" maxlength="7" autocomplete="one-time-code" required autofocus>
                <button class="primary-button" type="submit">Verify account <span aria-hidden="true">&#8594;</span></button>
            </form>
            <p class="switch-auth">Need to start over? <a href="<?= htmlspecialchars(appUrl('signup'), ENT_QUOTES, 'UTF-8') ?>">Sign up again</a></p>
        </div>
    </section>
</main>
</body>
</html>
