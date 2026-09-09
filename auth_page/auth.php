<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/../config/urls.php';
require_once __DIR__ . '/../config/request_context.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function authOld(string $key): string
{
    return htmlspecialchars((string) ($_POST[$key] ?? ''), ENT_QUOTES, 'UTF-8');
}

function authInput(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function handleAuthRequest(string $mode): array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return ['errors' => [], 'notice' => null];
    }

    if (empty($_SESSION['csrf_token']) || empty($_POST['csrf_token'])
        || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        return ['errors' => ['Your form expired. Please try again.'], 'notice' => null];
    }

    $errors = [];
    $database = null;

    if ($mode === 'login') {
        $identifier = authInput('identifier');
        $normalizedIdentifier = strtolower($identifier);
        $password = (string) ($_POST['password'] ?? '');

        if ($identifier === '') {
            $errors[] = 'Enter your username or email.';
        }
        if ($password === '') {
            $errors[] = 'Enter your password.';
        }

        if (!$errors) {
            try {
                $database = db();
                $statement = $database->prepare(
                    'SELECT id, username, password_hash, status FROM users
                     WHERE LOWER(email) = :email OR LOWER(username) = :username
                     LIMIT 1'
                );
                $statement->execute([
                    'email' => $normalizedIdentifier,
                    'username' => $normalizedIdentifier,
                ]);
                $user = $statement->fetch();

                if (!$user || !password_verify($password, $user['password_hash']) || $user['status'] !== 'active') {
                    $errors[] = 'Those login details are not recognised.';
                } else {
                    session_regenerate_id(true);
                    $sessionId = bin2hex(random_bytes(32));
                    $sessionStatement = $database->prepare(
                        'INSERT INTO sessions (id, user_id, ip_address, user_agent, expires_at) VALUES (:id, :user_id, :ip, :agent, :expires_at)'
                    );
                    $sessionStatement->execute([
                        'id' => $sessionId,
                        'user_id' => $user['id'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                        'agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                        'expires_at' => (new DateTimeImmutable('+30 days'))->format('Y-m-d H:i:s'),
                    ]);
                    $historyStatement = $database->prepare(
                        'INSERT INTO user_login_history
                            (user_id, session_id, device_name, ip_hash, location, user_agent, action, created_at)
                         VALUES
                            (:user_id, :session_id, :device_name, :ip_hash, :location, :user_agent, :action, NOW())'
                    );
                    $historyStatement->execute([
                        'user_id' => $user['id'],
                        'session_id' => $sessionId,
                        'device_name' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device'), 0, 120),
                        'ip_hash' => hash('sha256', (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown')),
                        'location' => requestLocation(),
                        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                        'action' => 'login',
                    ]);
                    $database->prepare('UPDATE users SET last_login_at = NOW() WHERE id = :id')->execute(['id' => $user['id']]);
                    $_SESSION['user_id'] = (int) $user['id'];
                    $_SESSION['auth_session_id'] = $sessionId;
                    header('Location: ' . appUrl('home'));
                    exit;
                }
            } catch (PDOException $exception) {
                error_log($exception->getMessage());
                $errors[] = 'We could not reach the database. Check your configuration and try again.';
            }
        }
    }

    if ($mode === 'signup') {
        $displayName = authInput('display_name');
        $username = authInput('username');
        $email = strtolower(authInput('email'));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');
        $termsAccepted = isset($_POST['terms']) && $_POST['terms'] === '1';

        if ($displayName === '' || strlen($displayName) > 50) {
            $errors[] = 'Display name must be between 1 and 50 characters.';
        }
        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            $errors[] = 'Username must be 3 to 30 characters using letters, numbers, or underscores.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
            $errors[] = 'Enter a valid email address.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'Passwords do not match.';
        }
        if (!$termsAccepted) {
            $errors[] = 'You must agree to the Terms of Service and Privacy Policy.';
        }

        if (!$errors) {
            try {
                $database = db();
                $duplicateStatement = $database->prepare(
                    'SELECT u.username, u.email, p.display_name
                     FROM users u
                     LEFT JOIN user_profiles p ON p.user_id = u.id
                     WHERE u.username = :username OR u.email = :email OR p.display_name = :display_name
                     LIMIT 1'
                );
                $duplicateStatement->execute([
                    'username' => $username,
                    'email' => $email,
                    'display_name' => $displayName,
                ]);
                $duplicate = $duplicateStatement->fetch();
                if ($duplicate) {
                    if ($duplicate['username'] === $username) {
                        $errors[] = 'That username is already in use.';
                    } elseif ($duplicate['email'] === $email) {
                        $errors[] = 'That email is already in use.';
                    } else {
                        $errors[] = 'That display name is already in use.';
                    }
                }

                if ($errors) {
                    return ['errors' => $errors, 'notice' => null];
                }

                $database->beginTransaction();
                $userStatement = $database->prepare(
                    'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
                );
                $userStatement->execute([
                    'username' => $username,
                    'email' => $email,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $userId = (int) $database->lastInsertId();
                $profileStatement = $database->prepare(
                    'INSERT INTO user_profiles (user_id, display_name) VALUES (:user_id, :display_name)'
                );
                $profileStatement->execute(['user_id' => $userId, 'display_name' => $displayName]);
                $token = (string) random_int(1000000, 9999999);
                $tokenStatement = $database->prepare(
                    'INSERT INTO email_verification_tokens (token, user_id, expires_at) VALUES (:token, :user_id, :expires_at)'
                );
                $tokenStatement->execute([
                    'token' => $token,
                    'user_id' => $userId,
                    'expires_at' => (new DateTimeImmutable('+24 hours'))->format('Y-m-d H:i:s'),
                ]);
                if (!sendVerificationEmail($email, $displayName, $token)) {
                    throw new RuntimeException('Verification email could not be sent.');
                }
                $database->commit();
                $_SESSION['pending_verification_email'] = $email;
                header('Location: ' . appUrl('verify'));
                exit;
            } catch (PDOException $exception) {
                if ($database instanceof PDO && $database->inTransaction()) {
                    $database->rollBack();
                }
                $errors[] = $exception->errorInfo[1] === 1062
                    ? 'That username or email is already in use.'
                    : 'We could not create your account. Check your database configuration and try again.';
            } catch (Throwable $exception) {
                if ($database instanceof PDO && $database->inTransaction()) {
                    $database->rollBack();
                }
                error_log($exception->getMessage());
                $errors[] = 'We could not send the verification code. Check your mail settings and try again.';
            }
        }
    }

    return ['errors' => $errors, 'notice' => null];
}
