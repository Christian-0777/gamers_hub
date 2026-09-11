<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/urls.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['auth_session_id'])) {
    header('Location: ' . appUrl('login'));
    exit;
}

$sessionStatement = db()->prepare(
    'SELECT user_id FROM sessions
     WHERE id = :session_id AND user_id = :user_id AND expires_at > NOW() LIMIT 1'
);
$sessionStatement->execute([
    'session_id' => $_SESSION['auth_session_id'],
    'user_id' => $_SESSION['user_id'],
]);

if (!$sessionStatement->fetch()) {
    unset($_SESSION['user_id'], $_SESSION['auth_session_id']);
    header('Location: ' . appUrl('login'));
    exit;
}

$userStatement = db()->prepare(
    'SELECT u.username, p.display_name, p.avatar_url
     FROM users u INNER JOIN user_profiles p ON p.user_id = u.id
     WHERE u.id = :user_id LIMIT 1'
);
$userStatement->execute(['user_id' => $_SESSION['user_id']]);
$user = $userStatement->fetch() ?: [
    'username' => 'gamer',
    'display_name' => 'Gamer',
    'avatar_url' => null,
];

$name = (string) $user['display_name'];
$initials = strtoupper(substr($name, 0, 1) . substr((string) $user['username'], 0, 1));
$avatar = $user['avatar_url'] ?: appUrl('assets/icons/profile.png');
$profileUrl = appUrl('@' . $user['username']);
$dashboardLayout = true;
$dashboardActivePage = 'message';

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GamersHUB | Messages</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars(appUrl('assets/icons/logo.png'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/layout.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/shared/coming_soon.css'), ENT_QUOTES, 'UTF-8') ?>">
    <link rel="stylesheet" href="<?= htmlspecialchars(appUrl('assets/css/message.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="dashboard-page message-page">
    <div class="dashboard-backdrop" id="dashboardBackdrop"></div>
    <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

    <div class="dashboard-main" id="dashboardMain">
        <?php require __DIR__ . '/../includes/header.php'; ?>
        <main class="messenger" data-api-url="<?= htmlspecialchars(appUrl('api/messages.php'), ENT_QUOTES, 'UTF-8') ?>" data-current-user="<?= (int) $_SESSION['user_id'] ?>" data-default-avatar="<?= htmlspecialchars(appUrl('assets/icons/profile.png'), ENT_QUOTES, 'UTF-8') ?>">
            <aside class="conversation-sidebar">
                <div class="sidebar-heading">
                    <div><span class="eyebrow">Community</span><h1>Messages</h1></div>
                    <button class="message-icon-button" type="button" aria-label="Start a new message" title="Start a new message"><span class="material-symbols-rounded" aria-hidden="true">edit_square</span></button>
                </div>
                <label class="conversation-search"><span class="material-symbols-rounded" aria-hidden="true">search</span><input id="conversationSearch" type="search" placeholder="Search conversations" autocomplete="off"></label>
                <div class="conversation-list" id="conversationList" aria-live="polite"><div class="message-loading">Loading conversations...</div></div>
            </aside>

            <section class="chat-area" id="chatArea" aria-label="Active conversation">
                <div class="chat-empty" id="chatEmpty"><span class="material-symbols-rounded" aria-hidden="true">forum</span><h2>Your conversations</h2><p>Select a conversation to start chatting.</p></div>
                <div class="chat-content" id="chatContent" hidden>
                    <header class="chat-header">
                        <div class="chat-user">
                            <button class="message-icon-button mobile-back" id="mobileBack" type="button" aria-label="Back to conversations"><span class="material-symbols-rounded" aria-hidden="true">arrow_back</span></button>
                            <div class="chat-avatar-wrap"><img id="chatAvatar" class="chat-avatar" src="<?= htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') ?>" alt=""><span id="chatOnlineDot" class="online-dot"></span></div>
                            <div><h2 id="chatUserName">Conversation</h2><p id="chatUserStatus">Offline</p></div>
                        </div>
                        <button class="message-icon-button" type="button" aria-label="Conversation details" title="Conversation details"><span class="material-symbols-rounded" aria-hidden="true">info</span></button>
                    </header>
                    <div class="message-container" id="messageContainer" aria-live="polite"></div>
                    <form class="message-composer" id="messageForm">
                        <button class="composer-button" type="button" aria-label="Add attachment" title="Add attachment"><span class="material-symbols-rounded" aria-hidden="true">add_circle</span></button>
                        <div class="message-input-wrap"><textarea id="messageInput" rows="1" maxlength="4000" placeholder="Write a message..."></textarea></div>
                        <button class="composer-button" id="emojiButton" type="button" aria-label="Add emoji" title="Add emoji"><span class="material-symbols-rounded" aria-hidden="true">mood</span></button>
                        <button class="send-button" id="sendButton" type="submit" aria-label="Send message" disabled><span class="material-symbols-rounded" aria-hidden="true">send</span></button>
                    </form>
                </div>
            </section>
        </main>
        <?php require __DIR__ . '/../includes/shared_footer.php'; ?>
    </div>
    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/layout_functions.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(appUrl('assets/js/shared/coming_soon.js'), ENT_QUOTES, 'UTF-8') ?>"></script>
    <script src="<?= htmlspecialchars(appUrl('assets/js/message.js') . '?v=' . filemtime(__DIR__ . '/../assets/js/message.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
