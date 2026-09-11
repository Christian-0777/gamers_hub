-- ============================================================
-- GAMERS HUB SETTINGS MIGRATION
-- Adds profile privacy, notification, security, and login tracking
-- fields required by the settings/account page.
-- ============================================================

USE gamers_hub;

CREATE TABLE IF NOT EXISTS conversations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    is_group BOOLEAN NOT NULL DEFAULT FALSE,
    title VARCHAR(120) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_conversations_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversation_participants (
    conversation_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    last_read_message_id BIGINT UNSIGNED NULL,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (conversation_id, user_id),
    CONSTRAINT fk_conversation_participants_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_conversation_participants_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_participants_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_messages_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    CONSTRAINT fk_messages_sender FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_messages_conversation_created (conversation_id, created_at, id),
    INDEX idx_messages_sender (sender_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE user_profiles
    ADD COLUMN social_discord VARCHAR(500) NULL AFTER bio,
    ADD COLUMN social_tiktok VARCHAR(500) NULL AFTER social_discord,
    ADD COLUMN social_facebook VARCHAR(500) NULL AFTER social_tiktok,
    ADD COLUMN social_twitch VARCHAR(500) NULL AFTER social_facebook,
    ADD COLUMN social_kick VARCHAR(500) NULL AFTER social_twitch,
    ADD COLUMN social_github VARCHAR(500) NULL AFTER social_kick,
    ADD COLUMN social_spotify VARCHAR(500) NULL AFTER social_github,
    ADD COLUMN social_youtube VARCHAR(500) NULL AFTER social_spotify,
    ADD COLUMN social_apple_music VARCHAR(500) NULL AFTER social_youtube,
    ADD COLUMN social_steam VARCHAR(500) NULL AFTER social_apple_music,
    ADD COLUMN social_x VARCHAR(500) NULL AFTER social_steam,
    ADD COLUMN profile_visibility ENUM(
        'public',
        'friends_only',
        'followers_only',
        'private'
    ) NOT NULL DEFAULT 'public' AFTER bio,
    ADD COLUMN who_can_message ENUM(
        'everyone',
        'followers',
        'friends'
    ) NOT NULL DEFAULT 'everyone' AFTER profile_visibility,
    ADD COLUMN email_notifications BOOLEAN NOT NULL DEFAULT TRUE AFTER preferred_voice_chat,
    ADD COLUMN push_notifications BOOLEAN NOT NULL DEFAULT TRUE AFTER email_notifications,
    ADD COLUMN social_notifications BOOLEAN NOT NULL DEFAULT TRUE AFTER push_notifications,
    ADD COLUMN two_factor_enabled BOOLEAN NOT NULL DEFAULT FALSE AFTER social_notifications;

CREATE TABLE IF NOT EXISTS user_login_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    device_name VARCHAR(120) NOT NULL,
    ip_hash CHAR(64) NOT NULL,
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

    INDEX idx_login_history_user (user_id),
    INDEX idx_login_history_created_at (created_at)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

ALTER TABLE user_login_history
    ADD COLUMN session_id CHAR(64) NULL AFTER user_id,
    ADD COLUMN location VARCHAR(120) NULL AFTER ip_hash,
    ADD CONSTRAINT fk_login_history_session
        FOREIGN KEY (session_id)
        REFERENCES sessions(id)
        ON DELETE SET NULL,
    ADD INDEX idx_login_history_session (session_id);

-- Optional cleanup for existing rows if you want to backfill values.
-- UPDATE user_profiles
-- SET profile_visibility = 'public',
--     who_can_message = 'everyone',
--     email_notifications = TRUE,
--     push_notifications = TRUE,
--     social_notifications = TRUE,
--     two_factor_enabled = FALSE
-- WHERE profile_visibility IS NULL;

-- ============================================================
-- STORAGE CONVENTION FOR UPLOADED FILES
-- No schema change required because these values are already stored in:
--   - user_profiles.avatar_url
--   - user_profiles.cover_url
--   - post_media.media_url
-- ============================================================

SELECT
    'uploads/profile' AS profile_storage_path,
    'uploads/cover' AS cover_storage_path,
    'uploads/post' AS post_storage_path,
    'uploads/post/post_<username>_<post_id>' AS post_folder_pattern,
    'avatar_url / cover_url / media_url store the final public file path' AS storage_note;

-- Application convention:
-- 1. Profile image files are stored under the local uploads/profile/ path.
-- 2. Cover image files are stored under the local uploads/cover/ path.
-- 3. Each post image batch is stored under uploads/post/post_<username>_<post_id>/.
-- 4. The saved DB values should point to the final uploaded path, for example:
--    media/profile/profile_123.jpg
--    media/cover/cover_123.jpg
--    media/post/post_alex_42/image_1.jpg
