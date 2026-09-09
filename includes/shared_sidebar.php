<aside class="sidebar" id="sidebar">
    <nav class="main-nav" aria-label="Main navigation">
        <a class="nav-link active" href="<?= htmlspecialchars(appUrl('home'), ENT_QUOTES, 'UTF-8') ?>#feed">
            <span class="nav-icon">⌂</span>
            Home
        </a>

        <button class="nav-link coming-soon" type="button">
            <span class="nav-icon">◉</span>
            Streams
            <span class="nav-arrow">›</span>
        </button>

        <button class="nav-link coming-soon" type="button">
            <span class="nav-icon">♧</span>
            GClan
            <span class="nav-arrow">›</span>
        </button>

        <button class="nav-link coming-soon" type="button">
            <span class="nav-icon">✦</span>
            Discover
            <span class="nav-arrow">›</span>
        </button>

        <button class="nav-link coming-soon" type="button">
            <span class="nav-icon">▦</span>
            My Games
            <span class="nav-arrow">›</span>
        </button>

        <button class="nav-link coming-soon" type="button">
            <span class="nav-icon">◌</span>
            Messages
            <span class="nav-count">3</span>
        </button>

        <button class="nav-link coming-soon" type="button">
            <span class="nav-icon">♢</span>
            Notifications
            <span class="nav-count">5</span>
        </button>

        <a class="nav-link" href="<?= htmlspecialchars(appUrl('settings'), ENT_QUOTES, 'UTF-8') ?>">
            <span class="nav-icon">⚙</span>
            Settings
            <span class="nav-arrow">›</span>
        </a>
    </nav>

    <div class="sidebar-bottom">
        <div class="level-card">
            <div class="level-top">
                <span>YOUR LEVEL</span>
                <strong>08</strong>
            </div>

            <div class="level-track">
                <span></span>
            </div>

            <small>320 XP to next level</small>
        </div>

        <a class="help-link" href="#">
            ?
            <span>Help center</span>
        </a>
    </div>
</aside>