-- ============================================================
-- GAMERS HUB DATABASE
-- Social platform for gamers
-- MySQL / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS gamers_hub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gamers_hub;

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,

    status ENUM(
        'active',
        'suspended',
        'banned',
        'deactivated'
    ) NOT NULL DEFAULT 'active',

    email_verified_at DATETIME NULL,

    last_login_at DATETIME NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_users_status (status),
    INDEX idx_users_created_at (created_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 1.1 EMAIL VERIFICATION TOKENS
-- ============================================================

CREATE TABLE email_verification_tokens (
    token CHAR(7) PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    expires_at DATETIME NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_verification_tokens_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_verification_tokens_user (user_id),
    INDEX idx_verification_tokens_expires (expires_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 2. USER PROFILES
-- Public gamer information
-- ============================================================

CREATE TABLE user_profiles (
    user_id BIGINT UNSIGNED PRIMARY KEY,

    display_name VARCHAR(50) NOT NULL,

    bio VARCHAR(500) NULL,

    avatar_url VARCHAR(500) NULL,
    cover_url VARCHAR(500) NULL,

    location VARCHAR(100) NULL,

    gaming_style ENUM(
        'casual',
        'competitive',
        'both'
    ) NOT NULL DEFAULT 'both',

    preferred_voice_chat BOOLEAN NOT NULL DEFAULT TRUE,

    online_status ENUM(
        'online',
        'offline',
        'away',
        'do_not_disturb'
    ) NOT NULL DEFAULT 'offline',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_display_name (display_name),

    CONSTRAINT fk_profile_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 3. SESSIONS
-- Login sessions
-- ============================================================

CREATE TABLE sessions (
    id CHAR(64) PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,

    expires_at DATETIME NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_sessions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_expires (expires_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. GAMES
-- Master list of games
-- ============================================================

CREATE TABLE games (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    slug VARCHAR(150) NOT NULL UNIQUE,

    description TEXT NULL,

    cover_url VARCHAR(500) NULL,
    icon_url VARCHAR(500) NULL,

    developer VARCHAR(150) NULL,
    publisher VARCHAR(150) NULL,

    release_date DATE NULL,

    status ENUM(
        'active',
        'inactive'
    ) NOT NULL DEFAULT 'active',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_games_name (name),
    INDEX idx_games_status (status)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 5. GAME PLATFORMS
-- Platforms supported by each game
-- ============================================================

CREATE TABLE game_platforms (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    game_id BIGINT UNSIGNED NOT NULL,

    platform ENUM(
        'PC',
        'PlayStation',
        'Xbox',
        'Nintendo Switch',
        'Mobile',
        'Steam Deck',
        'Other'
    ) NOT NULL,

    CONSTRAINT fk_game_platform_game
        FOREIGN KEY (game_id)
        REFERENCES games(id)
        ON DELETE CASCADE,

    UNIQUE KEY unique_game_platform (
        game_id,
        platform
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6. USER GAMES
-- Games played / owned / favorited by users
-- ============================================================

CREATE TABLE user_games (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    game_id BIGINT UNSIGNED NOT NULL,

    status ENUM(
        'playing',
        'favorite',
        'played',
        'want_to_play'
    ) NOT NULL DEFAULT 'playing',

    hours_played DECIMAL(10,2) NULL,

    skill_level ENUM(
        'beginner',
        'intermediate',
        'advanced',
        'expert'
    ) NULL,

    rank_name VARCHAR(100) NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_user_games_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_user_games_game
        FOREIGN KEY (game_id)
        REFERENCES games(id)
        ON DELETE CASCADE,

    UNIQUE KEY unique_user_game (
        user_id,
        game_id
    ),

    INDEX idx_user_games_game (game_id),
    INDEX idx_user_games_user (user_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6.1 USER ONBOARDING PREFERENCES
-- ============================================================

CREATE TABLE user_gamer_types (
        user_id BIGINT UNSIGNED NOT NULL,
        gamer_type VARCHAR(40) NOT NULL,

        PRIMARY KEY (user_id, gamer_type),

        CONSTRAINT fk_user_gamer_types_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_platforms (
        user_id BIGINT UNSIGNED NOT NULL,
        platform VARCHAR(50) NOT NULL,

        PRIMARY KEY (user_id, platform),

        CONSTRAINT fk_user_platforms_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_goals (
        user_id BIGINT UNSIGNED NOT NULL,
        goal VARCHAR(60) NOT NULL,

        PRIMARY KEY (user_id, goal),

        CONSTRAINT fk_user_goals_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_content_preferences (
        user_id BIGINT UNSIGNED NOT NULL,
        content_type VARCHAR(60) NOT NULL,

        PRIMARY KEY (user_id, content_type),

        CONSTRAINT fk_user_content_preferences_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 7. FOLLOWERS
-- User following system
-- ============================================================

CREATE TABLE followers (
    follower_id BIGINT UNSIGNED NOT NULL,

    following_id BIGINT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        follower_id,
        following_id
    ),

    CONSTRAINT fk_followers_follower
        FOREIGN KEY (follower_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_followers_following
        FOREIGN KEY (following_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_followers_following (
        following_id
    ),

    INDEX idx_followers_follower (
        follower_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 8. POSTS
-- Main social media posts
-- ============================================================

CREATE TABLE posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    game_id BIGINT UNSIGNED NULL,

    post_type ENUM(
        'text',
        'achievement',
        'discussion',
        'question',
        'looking_for_players',
        'review'
    ) NOT NULL DEFAULT 'text',

    content TEXT NOT NULL,

    visibility ENUM(
        'public',
        'followers',
        'private'
    ) NOT NULL DEFAULT 'public',

    status ENUM(
        'published',
        'hidden',
        'deleted'
    ) NOT NULL DEFAULT 'published',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_posts_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_posts_game
        FOREIGN KEY (game_id)
        REFERENCES games(id)
        ON DELETE SET NULL,

    INDEX idx_posts_user_created (
        user_id,
        created_at
    ),

    INDEX idx_posts_game_created (
        game_id,
        created_at
    ),

    INDEX idx_posts_created (
        created_at
    ),

    INDEX idx_posts_status (
        status
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 9. POST MEDIA
-- Images / videos attached to posts
-- ============================================================

CREATE TABLE post_media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    post_id BIGINT UNSIGNED NOT NULL,

    media_type ENUM(
        'image',
        'video'
    ) NOT NULL,

    media_url VARCHAR(1000) NOT NULL,

    thumbnail_url VARCHAR(1000) NULL,

    file_size BIGINT UNSIGNED NULL,

    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,

    duration_seconds INT UNSIGNED NULL,

    sort_order INT UNSIGNED NOT NULL DEFAULT 0,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_post_media_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    INDEX idx_post_media_post (
        post_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 10. POST REACTIONS
-- Likes / reactions
-- ============================================================

CREATE TABLE post_reactions (
    post_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED NOT NULL,

    reaction_type ENUM(
        'like',
        'love',
        'fire',
        'laugh',
        'sad'
    ) NOT NULL DEFAULT 'like',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        post_id,
        user_id
    ),

    CONSTRAINT fk_reactions_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_reactions_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_reactions_user (
        user_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 11. COMMENTS
-- Post comments + replies
-- ============================================================

CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    post_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED NOT NULL,

    parent_id BIGINT UNSIGNED NULL,

    content TEXT NOT NULL,

    status ENUM(
        'published',
        'hidden',
        'deleted'
    ) NOT NULL DEFAULT 'published',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_comments_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_comments_parent
        FOREIGN KEY (parent_id)
        REFERENCES comments(id)
        ON DELETE CASCADE,

    INDEX idx_comments_post (
        post_id,
        created_at
    ),

    INDEX idx_comments_user (
        user_id
    ),

    INDEX idx_comments_parent (
        parent_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 12. POST SHARES
-- Sharing posts
-- ============================================================

CREATE TABLE post_shares (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    post_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_shares_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_shares_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_shares_post (
        post_id
    ),

    INDEX idx_shares_user (
        user_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. NOTIFICATIONS
-- ============================================================

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    actor_id BIGINT UNSIGNED NULL,

    type ENUM(
        'follow',
        'reaction',
        'comment',
        'share',
        'mention',
        'system'
    ) NOT NULL,

    post_id BIGINT UNSIGNED NULL,

    message VARCHAR(500) NULL,

    is_read BOOLEAN NOT NULL DEFAULT FALSE,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notifications_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notifications_actor
        FOREIGN KEY (actor_id)
        REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_notifications_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    INDEX idx_notifications_user_read (
        user_id,
        is_read,
        created_at
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 14. LFG POSTS
-- Looking For Group / Players
-- ============================================================

CREATE TABLE lfg_posts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    post_id BIGINT UNSIGNED NOT NULL UNIQUE,

    game_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED NOT NULL,

    platform VARCHAR(50) NULL,

    players_needed INT UNSIGNED NOT NULL DEFAULT 1,

    players_joined INT UNSIGNED NOT NULL DEFAULT 0,

    rank_required VARCHAR(100) NULL,

    voice_required BOOLEAN NOT NULL DEFAULT FALSE,

    region VARCHAR(100) NULL,

    status ENUM(
        'open',
        'full',
        'closed'
    ) NOT NULL DEFAULT 'open',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_lfg_post
        FOREIGN KEY (post_id)
        REFERENCES posts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_lfg_game
        FOREIGN KEY (game_id)
        REFERENCES games(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_lfg_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_lfg_game_status (
        game_id,
        status
    ),

    INDEX idx_lfg_user (
        user_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 15. LFG MEMBERS
-- Users joining an LFG post
-- ============================================================

CREATE TABLE lfg_members (
    lfg_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED NOT NULL,

    status ENUM(
        'pending',
        'accepted',
        'rejected',
        'left'
    ) NOT NULL DEFAULT 'pending',

    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        lfg_id,
        user_id
    ),

    CONSTRAINT fk_lfg_members_lfg
        FOREIGN KEY (lfg_id)
        REFERENCES lfg_posts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_lfg_members_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 16. COMMUNITIES
-- Future gaming communities / groups
-- ============================================================

CREATE TABLE communities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    owner_id BIGINT UNSIGNED NOT NULL,

    name VARCHAR(100) NOT NULL,

    slug VARCHAR(120) NOT NULL UNIQUE,

    description TEXT NULL,

    avatar_url VARCHAR(500) NULL,

    cover_url VARCHAR(500) NULL,

    visibility ENUM(
        'public',
        'private'
    ) NOT NULL DEFAULT 'public',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_communities_owner
        FOREIGN KEY (owner_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_communities_name (
        name
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 17. COMMUNITY MEMBERS
-- ============================================================

CREATE TABLE community_members (
    community_id BIGINT UNSIGNED NOT NULL,

    user_id BIGINT UNSIGNED NOT NULL,

    role ENUM(
        'member',
        'moderator',
        'admin'
    ) NOT NULL DEFAULT 'member',

    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (
        community_id,
        user_id
    ),

    CONSTRAINT fk_community_members_community
        FOREIGN KEY (community_id)
        REFERENCES communities(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_community_members_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_community_members_user (
        user_id
    )
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- SAMPLE GAME DATA
-- Optional starter data
-- ============================================================

INSERT INTO games
    (name, slug, description, developer, publisher)
VALUES
    (
        'Valorant',
        'valorant',
        'Free-to-play competitive tactical shooter.',
        'Riot Games',
        'Riot Games'
    ),
    (
        'Counter-Strike 2',
        'counter-strike-2',
        'Competitive multiplayer tactical first-person shooter.',
        'Valve',
        'Valve'
    ),
    (
        'Minecraft',
        'minecraft',
        'Sandbox game focused on exploration and creation.',
        'Mojang Studios',
        'Mojang Studios'
    ),
    (
        'Grand Theft Auto V',
        'grand-theft-auto-v',
        'Open-world action-adventure game.',
        'Rockstar North',
        'Rockstar Games'
    ),
    (
        'Dota 2',
        'dota-2',
        'Competitive multiplayer online battle arena.',
        'Valve',
        'Valve'
    ),
    (
        'League of Legends',
        'league-of-legends',
        'Competitive multiplayer online battle arena.',
        'Riot Games',
        'Riot Games'
    ),
    (
        'Call of Duty',
        'call-of-duty',
        'First-person shooter franchise.',
        'Activision',
        'Activision'
    ),
    (
        'Euro Truck Simulator 2',
        'euro-truck-simulator-2',
        'Truck driving simulation game.',
        'SCS Software',
        'SCS Software'
    );


-- ============================================================
-- GAME PLATFORM DATA
-- ============================================================

INSERT INTO game_platforms (game_id, platform)
SELECT id, 'PC'
FROM games
WHERE slug IN (
    'valorant',
    'counter-strike-2',
    'minecraft',
    'grand-theft-auto-v',
    'dota-2',
    'league-of-legends',
    'call-of-duty',
    'euro-truck-simulator-2'
);


-- ============================================================
-- FINISH
-- ============================================================

SET FOREIGN_KEY_CHECKS = 1;