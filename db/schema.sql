-- ============================================================
-- GAMERS HUB DATABASE
-- Social platform for gamers
-- MySQL / MariaDB
-- ============================================================

CREATE DATABASE IF NOT EXISTS gamers_hub
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE gamers_hub;

SET time_zone = '+00:00';

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
-- 1.2 PASSWORD RESET TOKENS
-- ============================================================

CREATE TABLE password_reset_tokens (
    token_hash CHAR(64) PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,

    expires_at DATETIME NOT NULL,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_password_reset_tokens_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_password_reset_tokens_user (user_id),
    INDEX idx_password_reset_tokens_expires (expires_at)
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

    social_discord VARCHAR(500) NULL,
    social_tiktok VARCHAR(500) NULL,
    social_facebook VARCHAR(500) NULL,
    social_twitch VARCHAR(500) NULL,
    social_kick VARCHAR(500) NULL,
    social_github VARCHAR(500) NULL,
    social_spotify VARCHAR(500) NULL,
    social_youtube VARCHAR(500) NULL,
    social_apple_music VARCHAR(500) NULL,
    social_steam VARCHAR(500) NULL,
    social_x VARCHAR(500) NULL,

    profile_visibility ENUM(
        'public',
        'friends_only',
        'followers_only',
        'private'
    ) NOT NULL DEFAULT 'public',

    who_can_message ENUM(
        'everyone',
        'followers',
        'friends'
    ) NOT NULL DEFAULT 'everyone',

    avatar_url VARCHAR(500) NULL,
    cover_url VARCHAR(500) NULL,

    location VARCHAR(100) NULL,

    gaming_style ENUM(
        'casual',
        'competitive',
        'both'
    ) NOT NULL DEFAULT 'both',

    preferred_voice_chat BOOLEAN NOT NULL DEFAULT TRUE,

    email_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    push_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    social_notifications BOOLEAN NOT NULL DEFAULT TRUE,
    two_factor_enabled BOOLEAN NOT NULL DEFAULT FALSE,

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

CREATE TABLE user_login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    user_id BIGINT UNSIGNED NOT NULL,
    session_id CHAR(64) NULL,
    device_name VARCHAR(120) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
    location VARCHAR(120) NULL,
    user_agent TEXT NULL,
    action ENUM(
        'login',
        'logout',
        'security_check'
    ) NOT NULL DEFAULT 'login',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_login_history_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_login_history_session
        FOREIGN KEY (session_id)
        REFERENCES sessions(id)
        ON DELETE SET NULL,

    INDEX idx_login_history_user (user_id),
    INDEX idx_login_history_session (session_id),
    INDEX idx_login_history_created_at (created_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 4. GAME CATALOG
-- Master list of games
-- ============================================================

CREATE TABLE IF NOT EXISTS game_catalog (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    source ENUM('steam', 'epic', 'multi', 'other') NOT NULL DEFAULT 'multi',
    external_id VARCHAR(255) NULL,
    cover_url VARCHAR(1000) NULL,
    icon_url VARCHAR(1000) NULL,
    artwork_url VARCHAR(1000) NULL,
    developer VARCHAR(255) NULL,
    publisher VARCHAR(255) NULL,
    release_date DATE NULL,
    description TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_game_catalog_slug (slug),
    KEY idx_game_catalog_name (name),
    KEY idx_game_catalog_source (source),
    KEY idx_game_catalog_external (source, external_id),
    KEY idx_game_catalog_active_name (is_active, name)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS company_catalog (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_company_catalog_name (name),
    KEY idx_company_catalog_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_companies (
    game_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    role ENUM('developer', 'publisher') NOT NULL,
    PRIMARY KEY (game_id, company_id, role),
    CONSTRAINT fk_game_companies_game FOREIGN KEY (game_id) REFERENCES game_catalog(id) ON DELETE CASCADE,
    CONSTRAINT fk_game_companies_company FOREIGN KEY (company_id) REFERENCES company_catalog(id) ON DELETE CASCADE,
    KEY idx_game_companies_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_companies (
    user_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    role ENUM('developer', 'publisher') NOT NULL DEFAULT 'developer',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, company_id, role),
    CONSTRAINT fk_user_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_companies_company FOREIGN KEY (company_id) REFERENCES company_catalog(id) ON DELETE CASCADE,
    KEY idx_user_companies_company (company_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_update_sources (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    game_id BIGINT UNSIGNED NOT NULL,
    source_name VARCHAR(100) NOT NULL,
    source_type ENUM('rss', 'atom', 'api', 'json', 'html') NOT NULL DEFAULT 'rss',
    feed_url VARCHAR(500) NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    last_checked_at DATETIME NULL,
    last_success_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_game_source (game_id, source_name),
    KEY idx_enabled (enabled),
    CONSTRAINT fk_update_source_game FOREIGN KEY (game_id) REFERENCES game_catalog(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_updates (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    game_id BIGINT UNSIGNED NOT NULL,
    source_id BIGINT UNSIGNED NOT NULL,
    version VARCHAR(50) DEFAULT NULL,
    update_type ENUM('release', 'open_beta', 'experimental_beta', 'patch', 'news', 'dlc', 'unknown') NOT NULL DEFAULT 'unknown',
    title VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    source_url VARCHAR(500) NOT NULL,
    source_guid VARCHAR(500) DEFAULT NULL,
    source_hash CHAR(64) NOT NULL,
    published_at DATETIME DEFAULT NULL,
    fetched_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_source_hash (source_hash),
    KEY idx_game_id (game_id),
    KEY idx_source_id (source_id),
    KEY idx_version (version),
    KEY idx_update_type (update_type),
    KEY idx_published_at (published_at),
    CONSTRAINT fk_game_updates_game FOREIGN KEY (game_id) REFERENCES game_catalog(id) ON DELETE CASCADE,
    CONSTRAINT fk_game_updates_source FOREIGN KEY (source_id) REFERENCES game_update_sources(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


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
        REFERENCES game_catalog(id)
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
        REFERENCES game_catalog(id)
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
-- 6.1 USER DEVELOPERS
-- Developers a user follows or prefers
-- ============================================================

CREATE TABLE user_developers (
        user_id BIGINT UNSIGNED NOT NULL,

        developer VARCHAR(150) NOT NULL,

        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (user_id, developer),

        CONSTRAINT fk_user_developers_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE,

        INDEX idx_user_developers_developer (developer)
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 6.2 USER ONBOARDING PREFERENCES
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
-- 7.1 MESSAGES
-- Direct and group conversations
-- ============================================================

CREATE TABLE conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    is_group BOOLEAN NOT NULL DEFAULT FALSE,
    title VARCHAR(120) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_conversations_updated_at (updated_at)
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;

CREATE TABLE conversation_participants (
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    last_read_message_id BIGINT UNSIGNED NULL,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (conversation_id, user_id),

    CONSTRAINT fk_conversation_participants_conversation
        FOREIGN KEY (conversation_id)
        REFERENCES conversations(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_conversation_participants_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_conversation_participants_user (user_id)
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_messages_conversation
        FOREIGN KEY (conversation_id)
        REFERENCES conversations(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_messages_sender
        FOREIGN KEY (sender_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    INDEX idx_messages_conversation_created (conversation_id, created_at, id),
    INDEX idx_messages_sender (sender_id)
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

    topic_type ENUM(
        'game',
        'developer',
        'publisher'
    ) NULL,

    topic_name VARCHAR(255) NULL,

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
        'friends',
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
        REFERENCES game_catalog(id)
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
-- 12. COMMENT REACTIONS
-- Reactions on comments
-- ============================================================

CREATE TABLE comment_reactions (
        comment_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

        PRIMARY KEY (comment_id, user_id),

        CONSTRAINT fk_comment_reactions_comment
                FOREIGN KEY (comment_id)
                REFERENCES comments(id)
                ON DELETE CASCADE,

        CONSTRAINT fk_comment_reactions_user
                FOREIGN KEY (user_id)
                REFERENCES users(id)
                ON DELETE CASCADE,

        INDEX idx_comment_reactions_user (user_id)
) ENGINE=InnoDB
    DEFAULT CHARSET=utf8mb4
    COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- 13. POST SHARES
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
        REFERENCES game_catalog(id)
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

INSERT INTO game_catalog
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
FROM game_catalog
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