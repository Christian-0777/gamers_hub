-- ============================================================
-- GAMERS HUB SETTINGS MIGRATION
-- Adds profile privacy, notification, security, and login tracking
-- fields required by the settings/account page.
-- ============================================================

USE gamers_hub;

ALTER TABLE user_profiles
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
    'uploads/profile' AS profile_storage_dir,
    'uploads/cover' AS cover_storage_dir,
    'uploads/post' AS post_storage_dir,
    'uploads/post/post_<username>_<post_id>' AS post_folder_pattern,
    'avatar_url / cover_url / media_url store the final public file path' AS storage_note;

-- Application convention:
-- 1. Profile image files are stored under uploads/profile/
-- 2. Cover image files are stored under uploads/cover/
-- 3. Each post image batch is stored under uploads/post/post_<username>_<post_id>/
-- 4. The saved DB values should point to the final uploaded path, for example:
--    uploads/profile/user_123.jpg
--    uploads/cover/user_123_cover.jpg
--    uploads/post/post_alex_42/image_1.jpg
