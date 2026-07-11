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

-- =====================================================
-- Schema migrations for additional fields
-- =====================================================
ALTER TABLE webhooks ADD COLUMN IF NOT EXISTS max_connections INT UNSIGNED NOT NULL DEFAULT 40;
ALTER TABLE webhooks ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45) NULL;
ALTER TABLE webhooks ADD COLUMN IF NOT EXISTS last_synchronization_error_date TIMESTAMP NULL;
ALTER TABLE chat_members ADD COLUMN IF NOT EXISTS restricted_until TIMESTAMP NULL;
ALTER TABLE chat_members ADD COLUMN IF NOT EXISTS restricted_permissions JSON NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS caption_entities JSON NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS parse_mode VARCHAR(32) NULL;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS show_caption_above_media BOOLEAN NOT NULL DEFAULT 0;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS disable_notification BOOLEAN NOT NULL DEFAULT 0;
ALTER TABLE messages ADD COLUMN IF NOT EXISTS message_thread_id BIGINT UNSIGNED NULL;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS first_name VARCHAR(255) NULL;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS last_name VARCHAR(255) NULL;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS invite_link VARCHAR(512) NULL;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS linked_chat_id BIGINT UNSIGNED NULL;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS slow_mode_delay INT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS message_auto_delete_time INT UNSIGNED NULL;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS has_protected_content BOOLEAN NOT NULL DEFAULT 0;
ALTER TABLE chats ADD COLUMN IF NOT EXISTS has_visible_history BOOLEAN NOT NULL DEFAULT 1;

-- =====================================================
-- Bot Accounts (bots registered on this platform)
-- Bot Accounts (bots registered on this platform)
-- =====================================================
CREATE TABLE IF NOT EXISTS bot_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL DEFAULT '',
    username VARCHAR(64) NULL UNIQUE,
    description TEXT NULL,
    short_description VARCHAR(120) NULL,
    about TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT 1,
    can_join_groups BOOLEAN NOT NULL DEFAULT 1,
    can_read_all_group_messages BOOLEAN NOT NULL DEFAULT 0,
    supports_inline_queries BOOLEAN NOT NULL DEFAULT 0,
    has_main_web_app BOOLEAN NOT NULL DEFAULT 0,
    can_connect_to_business BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_bot_token (token),
    INDEX idx_bot_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Sticker Sets
-- =====================================================
CREATE TABLE IF NOT EXISTS sticker_sets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    sticker_type ENUM('regular', 'mask', 'custom_emoji') NOT NULL DEFAULT 'regular',
    is_animated BOOLEAN NOT NULL DEFAULT 0,
    is_video BOOLEAN NOT NULL DEFAULT 0,
    thumbnail_file_id BIGINT UNSIGNED NULL,
    owner_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ss_name (name),
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Stickers
-- =====================================================
CREATE TABLE IF NOT EXISTS stickers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    set_id BIGINT UNSIGNED NOT NULL,
    file_id VARCHAR(64) NOT NULL,
    file_unique_id VARCHAR(64) NOT NULL,
    type ENUM('regular', 'mask', 'custom_emoji') NOT NULL DEFAULT 'regular',
    width INT UNSIGNED NOT NULL DEFAULT 512,
    height INT UNSIGNED NOT NULL DEFAULT 512,
    is_animated BOOLEAN NOT NULL DEFAULT 0,
    is_video BOOLEAN NOT NULL DEFAULT 0,
    emoji VARCHAR(255) NULL,
    file_size BIGINT UNSIGNED NULL DEFAULT 0,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_stickers_set (set_id),
    FOREIGN KEY (set_id) REFERENCES sticker_sets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Polls
-- =====================================================
CREATE TABLE IF NOT EXISTS polls (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message_id BIGINT UNSIGNED NULL,
    question TEXT NOT NULL,
    options JSON NOT NULL,
    is_anonymous BOOLEAN NOT NULL DEFAULT 1,
    type ENUM('regular', 'quiz') NOT NULL DEFAULT 'regular',
    allows_multiple_answers BOOLEAN NOT NULL DEFAULT 0,
    correct_option_id INT UNSIGNED NULL,
    explanation TEXT NULL,
    explanation_entities JSON NULL,
    open_period INT UNSIGNED NULL,
    close_date TIMESTAMP NULL,
    is_closed BOOLEAN NOT NULL DEFAULT 0,
    total_voter_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_polls_message (message_id),
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Poll Votes
-- =====================================================
CREATE TABLE IF NOT EXISTS poll_votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    poll_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    option_ids JSON NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_poll_vote (poll_id, user_id),
    INDEX idx_pv_poll (poll_id),
    FOREIGN KEY (poll_id) REFERENCES polls(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Inline Queries
-- =====================================================
CREATE TABLE IF NOT EXISTS inline_queries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    query TEXT NOT NULL,
    offset_val VARCHAR(64) NULL,
    chat_type VARCHAR(32) NULL,
    location JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_iq_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Callback Queries
-- =====================================================
CREATE TABLE IF NOT EXISTS callback_queries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    message_id BIGINT UNSIGNED NULL,
    chat_id BIGINT UNSIGNED NULL,
    data TEXT NULL,
    game_short_name VARCHAR(64) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cq_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Invoice / Payment
-- =====================================================
CREATE TABLE IF NOT EXISTS invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    payload VARCHAR(128) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'XTR',
    total_amount BIGINT UNSIGNED NOT NULL DEFAULT 0,
    prices JSON NULL,
    max_tip_amount BIGINT UNSIGNED NULL,
    suggested_tip_amounts JSON NULL,
    provider_token VARCHAR(255) NULL,
    start_parameter VARCHAR(64) NULL,
    is_paid BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_inv_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Game Scores
-- =====================================================
CREATE TABLE IF NOT EXISTS game_scores (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    game_short_name VARCHAR(64) NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    chat_id BIGINT UNSIGNED NULL,
    score BIGINT UNSIGNED NOT NULL DEFAULT 0,
    force BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_game_score (game_short_name, user_id, chat_id),
    INDEX idx_gs_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Chat Invite Links
-- =====================================================
CREATE TABLE IF NOT EXISTS invite_links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    creator_id BIGINT UNSIGNED NOT NULL,
    invite_link VARCHAR(512) NOT NULL UNIQUE,
    name VARCHAR(255) NULL,
    expire_date TIMESTAMP NULL,
    member_limit INT UNSIGNED NULL,
    creates_join_request BOOLEAN NOT NULL DEFAULT 0,
    is_primary BOOLEAN NOT NULL DEFAULT 0,
    is_revoked BOOLEAN NOT NULL DEFAULT 0,
    pending_join_request_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_il_chat (chat_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Star Transactions (Telegram Stars economy)
-- =====================================================
CREATE TABLE IF NOT EXISTS star_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    amount BIGINT NOT NULL DEFAULT 0,
    type ENUM('charge', 'refund', 'purchase') NOT NULL DEFAULT 'charge',
    description TEXT NULL,
    invoice_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_st_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Business Accounts
-- =====================================================
CREATE TABLE IF NOT EXISTS business_accounts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NULL,
    bio TEXT NULL,
    username VARCHAR(64) NULL,
    profile_photo_file_id BIGINT UNSIGNED NULL,
    intro TEXT NULL,
    location JSON NULL,
    opening_hours JSON NULL,
    gift_settings JSON NULL,
    is_active BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ba_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- User Gifts
-- =====================================================
CREATE TABLE IF NOT EXISTS user_gifts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    gift_id VARCHAR(64) NOT NULL,
    text TEXT NULL,
    emoji VARCHAR(32) NULL,
    is_upgraded BOOLEAN NOT NULL DEFAULT 0,
    can_be_transferred BOOLEAN NOT NULL DEFAULT 0,
    can_be_upgraded BOOLEAN NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ug_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Chat Boosts
-- =====================================================
CREATE TABLE IF NOT EXISTS chat_boosts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chat_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    source VARCHAR(64) NOT NULL DEFAULT 'premium',
    expire_date TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cb_chat (chat_id),
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Stories
-- =====================================================
CREATE TABLE IF NOT EXISTS stories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    chat_id BIGINT UNSIGNED NOT NULL,
    media_file_id VARCHAR(64) NOT NULL,
    caption TEXT NULL,
    entities JSON NULL,
    parse_mode VARCHAR(32) NULL,
    is_pinned BOOLEAN NOT NULL DEFAULT 0,
    expire_date TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    INDEX idx_stories_user (user_id),
    INDEX idx_stories_chat (chat_id),
    INDEX idx_stories_active (deleted_at, expire_date),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
