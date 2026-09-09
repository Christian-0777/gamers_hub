<?php
require_once __DIR__ . '/../config/db.php';

$headerUserStatement = db()->prepare('SELECT username, display_name, avatar_url FROM users u INNER JOIN user_profiles p ON p.user_id = u.id WHERE u.id = :user_id LIMIT 1');
$headerUserStatement->execute(['user_id' => $_SESSION['user_id'] ?? 0]);
$headerUser = $headerUserStatement->fetch() ?: ['username' => '', 'display_name' => 'Profile', 'avatar_url' => null];
$headerInitials = strtoupper(substr((string) $headerUser['display_name'], 0, 1) . substr((string) $headerUser['username'], 0, 1));
$headerAvatar = $headerUser['avatar_url'] ?: appUrl('assets/icons/profile.png');
?>
<header class="topbar">
  <div class="brand-wrap">
    <button
      class="icon-button menu-trigger"
      type="button"
      aria-label="Toggle navigation"
      aria-controls="sidebar"
      aria-expanded="true"
    >
      <span class="brand-mark">G</span>
      <span class="menu-lines"></span>
    </button>

    <a class="brand-name" href="<?= htmlspecialchars(appUrl(), ENT_QUOTES, 'UTF-8') ?>">
      Gamers<span>HUB</span>
    </a>
  </div>

  <label class="search-box" for="site-search">
    <span class="search-icon">⌕</span>
    <input
      id="site-search"
      type="search"
      placeholder="Search users, groups, or games"
    >
    <kbd>/</kbd>
  </label>

  <div class="top-actions">
    <button
      class="top-icon"
      type="button"
      aria-label="Notifications"
      data-notification-action
    >
      ♢
      <i></i>
    </button>

    <div class="profile-menu-wrap">
      <button
        class="profile-trigger"
        type="button"
        aria-label="Open profile options"
        aria-controls="profile-menu"
        aria-expanded="false"
      >
        <img
          class="avatar header-avatar"
          src="<?= htmlspecialchars($headerAvatar, ENT_QUOTES, 'UTF-8') ?>"
          alt="<?= htmlspecialchars($headerUser['display_name'], ENT_QUOTES, 'UTF-8') ?>"
        >
        <span class="profile-name"><?= htmlspecialchars($headerUser['display_name'], ENT_QUOTES, 'UTF-8') ?></span>
      </button>

      <div class="profile-menu" id="profile-menu" hidden>
        <a
          class="profile-option"
          href="<?= htmlspecialchars(appUrl('@' . $headerUser['username']), ENT_QUOTES, 'UTF-8') ?>"
        >
          Profile
          <span>›</span>
        </a>

        <a
          class="profile-option"
          href="<?= htmlspecialchars(appUrl('settings'), ENT_QUOTES, 'UTF-8') ?>"
        >
          Settings
          <span>›</span>
        </a>

        <a
          class="profile-option"
          href="<?= htmlspecialchars(appUrl('logout'), ENT_QUOTES, 'UTF-8') ?>"
        >
          Log out
          <span>›</span>
        </a>
      </div>
    </div>
  </div>
</header>