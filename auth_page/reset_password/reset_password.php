<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/urls.php';

if (str_ends_with(str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '')), '/auth_page/reset_password/reset_password.php')) {
	header('Location: ' . appUrl('login'), true, 302);
	exit;
}

if (session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

$username = (string) ($_GET['username'] ?? $_POST['username'] ?? '');
$token = (string) ($_GET['token'] ?? $_POST['token'] ?? $_SESSION['password_reset_token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'], $_GET['username'])) {
	$_SESSION['password_reset_token'] = (string) $_GET['token'];
	header('Location: ' . appUrl('reset/@' . rawurlencode($username)), true, 302);
	exit;
}

$errors = [];
$reset = false;
$tokenValid = false;

if ($token !== '' && $username !== '') {
	try {
		$database = db();
		$tokenStatement = $database->prepare(
			'SELECT t.user_id
			 FROM password_reset_tokens t
			 JOIN users u ON u.id = t.user_id
			 WHERE t.token_hash = :token_hash
			   AND LOWER(u.username) = :username
			   AND t.expires_at > NOW()
			 LIMIT 1'
		);
		$tokenStatement->execute([
			'token_hash' => hash('sha256', $token),
			'username' => strtolower($username),
		]);
		$tokenValid = (bool) $tokenStatement->fetch();
		if (!$tokenValid) {
			$errors[] = 'This reset link is invalid or has expired. Please request a new one.';
		}
	} catch (PDOException $exception) {
		error_log($exception->getMessage());
		$errors[] = 'We could not validate this reset link. Please request a new one.';
	}
} else {
	$errors[] = 'This reset link is missing its token. Please request a new one.';
}

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
	if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
		$errors[] = 'Your form expired. Please try again.';
	}

	$password = (string) ($_POST['password'] ?? '');
	$passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
	if (strlen($password) < 8) {
		$errors[] = 'Your new password must be at least 8 characters.';
	} elseif ($password !== $passwordConfirmation) {
		$errors[] = 'Your passwords do not match.';
	}

	if (!$errors) {
		try {
			$database = db();
			$statement = $database->prepare(
				'SELECT u.id
				 FROM password_reset_tokens t
				 JOIN users u ON u.id = t.user_id
				 WHERE t.token_hash = :token_hash
				   AND LOWER(u.username) = :username
				   AND t.expires_at > NOW()
				 LIMIT 1'
			);
			$statement->execute([
				'token_hash' => hash('sha256', $token),
				'username' => strtolower($username),
			]);
			$user = $statement->fetch();

			if ($user) {
				$database->beginTransaction();
				$database->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id')->execute([
					'password_hash' => password_hash($password, PASSWORD_DEFAULT),
					'id' => $user['id'],
				]);
				$database->prepare('DELETE FROM password_reset_tokens WHERE user_id = :user_id')->execute([
					'user_id' => $user['id'],
				]);
				$database->commit();
				unset($_SESSION['password_reset_token']);
				$reset = true;
			} else {
				$errors[] = 'This reset link is invalid or has expired. Please request a new one.';
			}
		} catch (PDOException $exception) {
			if (isset($database) && $database instanceof PDO && $database->inTransaction()) {
				$database->rollBack();
			}
			error_log($exception->getMessage());
			$errors[] = 'We could not reset your password. Please try again.';
		}
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Choose a new password | GamersHUB</title>
	<link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,400,0,0" rel="stylesheet">
	<link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/auth/auth.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body>
<main class="auth-shell">
	<section class="auth-intro">
		<a class="brand" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>"><img src="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>" alt=""> GamersHUB</a>
		<div class="intro-copy"><p class="eyebrow">ACCOUNT ACCESS</p><h1>A fresh start for your account.</h1><p>Choose a new password and get back to the people and games you love.</p></div>
		<p class="intro-footer">Keep your account yours.</p>
	</section>
	<section class="auth-panel">
		<div class="auth-card">
			<p class="eyebrow">NEW PASSWORD</p>
			<h2>Reset password</h2>
			<p class="subheading">Create a new password for your GamersHUB account.</p>
			<?php if ($errors): ?><div class="form-alert" role="alert"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
			<?php if ($tokenValid): ?><form method="post" action="<?= htmlspecialchars(appUrl('reset/@' . rawurlencode($username)), ENT_QUOTES, 'UTF-8') ?>" novalidate>
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
				<input type="hidden" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>">
				<input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
				<label for="password">New password</label>
				<div class="password-field"><input id="password" name="password" type="password" autocomplete="new-password" required><button type="button" class="password-toggle" data-toggle-password="password" aria-label="Show password" title="Show password"><span class="material-symbols-outlined" aria-hidden="true">visibility</span></button></div>
				<label for="password_confirmation">Confirm new password</label>
				<div class="password-field"><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required><button type="button" class="password-toggle" data-toggle-password="password_confirmation" aria-label="Show password" title="Show password"><span class="material-symbols-outlined" aria-hidden="true">visibility</span></button></div>
				<button class="primary-button" type="submit">Reset Password <span aria-hidden="true">&#8594;</span></button>
			</form><?php endif; ?>
		</div>
	</section>
</main>
<?php if ($reset): ?>
<div class="modal-backdrop" role="presentation">
	<section class="auth-modal" role="dialog" aria-modal="true" aria-labelledby="reset-complete-title">
		<p class="eyebrow">ALL SET</p>
		<h2 id="reset-complete-title">Your password has been reset</h2>
		<p>Your new password is ready. Log in to continue.</p>
		<a class="primary-button" href="<?= htmlspecialchars(appUrl('login'), ENT_QUOTES, 'UTF-8') ?>">Log in <span aria-hidden="true">&#8594;</span></a>
	</section>
</div>
<?php endif; ?>
<script src="<?= htmlspecialchars(appUrl('assets/js/auth/auth.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
</body>
</html>
