-- Messenger Backend — Database Schema
-- Mirrors Telegram's data model

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- Users & Auth
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    username VARCHAR(64) NULL UNIQUE,
    phone VARCHAR(20) NULL UNIQUE,
    first_name VARCHAR(255) NOT NULL DEFAULT '',
    last_name VARCHAR(255) NULL,
    password_hash VARCHAR(255) NULL,
    avatar_file_id BIGINT UNSIGNED NULL,
    is_bot BOOLEAN NOT NULL DEFAULT 0,
    is_premium BOOLEAN NOT NULL DEFAULT 0,
    language_code VARCHAR(10) NULL DEFAULT 'en',
    last_seen_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_users_phone (phone),
    INDEX idx_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sessions
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sessions_token (token),
    INDEX idx_sessions_user (user_id),
    INDEX idx_sessions_expires (expires_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Chats (private, group, supergroup, channel)
-- =====================================================
CREATE TABLE IF NOT EXISTS chats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('private', 'group', 'supergroup', 'channel') NOT NULL DEFAULT 'private',
    title VARCHAR(255) NULL DEFAULT '',
    username VARCHAR(64) NULL UNIQUE,
    description TEXT NULL,
    photo_file_id BIGINT UNSIGNED NULL,
    is_forum BOOLEAN NOT NULL DEFAULT 0,
    is_direct_messages BOOLEAN NOT NULL DEFAULT 0,
    member_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chats_type (type),
    INDEX idx_chats_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Chat Members
-- =====================================================
CREATE TABLE IF NOT EXISTS chat_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('member', 'admin', 'owner') NOT NULL DEFAULT 'member',
    status ENUM('active', 'left', 'kicked') NOT NULL DEFAULT 'active',
    custom_title VARCHAR(64) NULL,
    joined_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_chat_member (chat_id, user_id),
    INDEX idx_cm_user (user_id),
    INDEX idx_cm_chat (chat_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Chat Permissions
-- =====================================================
CREATE TABLE IF NOT EXISTS chat_permissions (
    chat_id BIGINT UNSIGNED PRIMARY KEY,
    can_send_messages BOOLEAN NOT NULL DEFAULT 1,
    can_send_media BOOLEAN NOT NULL DEFAULT 1,
    can_send_polls BOOLEAN NOT NULL DEFAULT 1,
    can_send_other BOOLEAN NOT NULL DEFAULT 1,
    can_add_members BOOLEAN NOT NULL DEFAULT 0,
    can_pin_messages BOOLEAN NOT NULL DEFAULT 0,
    can_change_info BOOLEAN NOT NULL DEFAULT 0,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Messages
-- =====================================================
CREATE TABLE IF NOT EXISTS messages (
    id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    sender_id BIGINT UNSIGNED NULL,
    message_thread_id BIGINT UNSIGNED NULL,
    reply_to_message_id BIGINT UNSIGNED NULL,
    forward_from_id BIGINT UNSIGNED NULL,
    text TEXT NULL,
    caption TEXT NULL,
    content_type ENUM(
        'text', 'photo', 'video', 'audio', 'document', 'sticker',
        'voice', 'video_note', 'location', 'contact', 'poll',
        'dice', 'game', 'animation', 'venue', 'invoice', 'live_photo',
        'checklist', 'rich_message'
    ) NOT NULL DEFAULT 'text',
    content_data JSON NULL,
    entities JSON NULL,
    reply_markup JSON NULL,
    edit_date TIMESTAMP NULL,
    is_protected BOOLEAN NOT NULL DEFAULT 0,
    views INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_msg_chat (chat_id),
    INDEX idx_msg_sender (sender_id),
    INDEX idx_msg_created (created_at),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Media (files, photos, videos, documents)
-- =====================================================
CREATE TABLE IF NOT EXISTS media (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    file_id VARCHAR(64) NOT NULL UNIQUE,
    file_unique_id VARCHAR(64) NOT NULL,
    file_path VARCHAR(512) NULL,
    file_size BIGINT UNSIGNED NULL DEFAULT 0,
    mime_type VARCHAR(128) NULL,
    width INT UNSIGNED NULL,
    height INT UNSIGNED NULL,
    duration INT UNSIGNED NULL,
    thumb_file_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_media_file_id (file_id),
    INDEX idx_media_unique (file_unique_id),
    INDEX idx_media_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Updates (long polling queue)
-- =====================================================
CREATE TABLE IF NOT EXISTS updates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    update_type VARCHAR(64) NOT NULL,
    payload JSON NOT NULL,
    is_read BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_updates_user_read (user_id, is_read),
    INDEX idx_updates_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Webhooks
-- =====================================================
CREATE TABLE IF NOT EXISTS webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    url VARCHAR(512) NOT NULL,
    secret_token VARCHAR(64) NULL,
    allowed_updates JSON NULL,
    has_custom_certificate BOOLEAN NOT NULL DEFAULT 0,
    last_error TEXT NULL,
    last_error_date TIMESTAMP NULL,
    pending_update_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhooks_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Bot Commands
-- =====================================================
CREATE TABLE IF NOT EXISTS bot_commands (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bot_id BIGINT UNSIGNED NOT NULL,
    command VARCHAR(64) NOT NULL,
    description VARCHAR(256) NOT NULL DEFAULT '',
    scope_type VARCHAR(32) NOT NULL DEFAULT 'default',
    scope_chat_id BIGINT UNSIGNED NULL,
    language_code VARCHAR(10) NOT NULL DEFAULT '',
    INDEX idx_bc_bot (bot_id),
    FOREIGN KEY (bot_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Forum Topics
-- =====================================================
CREATE TABLE IF NOT EXISTS forum_topics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL DEFAULT 'General',
    icon_color INT UNSIGNED NULL,
    icon_custom_emoji_id VARCHAR(64) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ft_chat (chat_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Message Reactions
-- =====================================================
CREATE TABLE IF NOT EXISTS message_reactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    reaction_type ENUM('emoji', 'custom_emoji') NOT NULL DEFAULT 'emoji',
    emoji VARCHAR(32) NULL,
    custom_emoji_id VARCHAR(64) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_reaction (message_id, user_id),
    INDEX idx_reactions_msg (message_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Pinned Messages
-- =====================================================
CREATE TABLE IF NOT EXISTS pinned_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    message_id BIGINT UNSIGNED NOT NULL,
    pinned_by BIGINT UNSIGNED NULL,
    pinned_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_pinned (chat_id),
    INDEX idx_pm_chat (chat_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
