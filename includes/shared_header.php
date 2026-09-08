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
        <span class="avatar avatar-coral">AK</span>
        <span class="profile-name">Alex Kim</span>
      </button>

      <div class="profile-menu" id="profile-menu" hidden>
        <button
          class="profile-option"
          type="button"
          data-profile-action="Profile"
        >
          Profile
          <span>›</span>
        </button>

        <button
          class="profile-option"
          type="button"
          data-profile-action="Log out"
        >
          Log out
          <span>›</span>
        </button>
      </div>
    </div>
  </div>
</header>