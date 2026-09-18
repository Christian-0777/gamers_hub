<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/env.php';

$mailer = new PHPMailer(true);

try {
	$mailer->isSMTP();
	$mailer->Host = env('MAIL_HOST', '');
	$mailer->Port = (int) env('MAIL_PORT', '587');
	$mailer->SMTPAuth = true;
	$mailer->Username = env('MAIL_USERNAME', '');
	$mailer->Password = env('MAIL_PASSWORD', '');
	$mailer->SMTPSecure = env('MAIL_ENCRYPTION', 'tls');
	$mailer->Timeout = 10;
	$mailer->smtpConnect();
	$mailer->smtpClose();
	echo "SMTP connection and authentication succeeded. No email was sent.\n";
} catch (Exception $exception) {
	fwrite(STDERR, "SMTP connection failed: {$exception->getMessage()}\n");
	exit(1);
}
