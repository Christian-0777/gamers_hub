<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';

function sendVerificationEmail(string $recipient, string $name, string $code): bool
{
	if (!env('MAIL_HOST')) {
		return false;
	}

	$mailer = new PHPMailer(true);

	try {
		$mailer->isSMTP();
		$mailer->Host = env('MAIL_HOST');
		$mailer->Port = (int) env('MAIL_PORT', '587');
		$mailer->SMTPAuth = true;
		$mailer->Username = env('MAIL_USERNAME', '');
		$mailer->Password = env('MAIL_PASSWORD', '');
		$mailer->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
		$mailer->setFrom(env('MAIL_FROM_ADDRESS', 'no-reply@example.com'), env('MAIL_FROM_NAME', 'GamersHUB'));
		$mailer->addAddress($recipient, $name);
		$mailer->isHTML(true);
		$mailer->Subject = 'Your GamersHUB verification code';
		$mailer->Body = '<p>Welcome to GamersHUB, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '.</p>'
			. '<p>Your seven-digit verification code is <strong>' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</strong>.</p>';
		$mailer->AltBody = "Your GamersHUB verification code is {$code}.";
		$mailer->send();
		return true;
	} catch (Exception $exception) {
		error_log('Verification email failed: ' . $exception->getMessage());
		return false;
	}
}

function sendAccountCreatedEmail(string $recipient, string $name): bool
{
	if (!env('MAIL_HOST')) {
		return false;
	}

	$mailer = new PHPMailer(true);

	try {
		$mailer->isSMTP();
		$mailer->Host = env('MAIL_HOST');
		$mailer->Port = (int) env('MAIL_PORT', '587');
		$mailer->SMTPAuth = true;
		$mailer->Username = env('MAIL_USERNAME', '');
		$mailer->Password = env('MAIL_PASSWORD', '');
		$mailer->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
		$mailer->setFrom(env('MAIL_FROM_ADDRESS', 'no-reply@example.com'), env('MAIL_FROM_NAME', 'GamersHUB'));
		$mailer->addAddress($recipient, $name);
		$mailer->isHTML(true);
		$mailer->Subject = 'Your Gamer account has been created';
		$mailer->Body = '<p>Your Gamer account has been created. Welcome to GamersHUB, ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '.</p>';
		$mailer->AltBody = 'Your Gamer account has been created. Welcome to GamersHUB.';
		$mailer->send();
		return true;
	} catch (Exception $exception) {
		error_log('Account-created email failed: ' . $exception->getMessage());
		return false;
	}
}
