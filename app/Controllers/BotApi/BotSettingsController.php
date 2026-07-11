<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Bot settings controller — getMe, getMyName, setMyCommands, etc.
 * Mirrors Telegram Bot API bot methods exactly
 */
class BotSettingsController extends BaseController
{
    /**
     * getMe — Get basic bot info
     */
    public function getMe(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        if ($bot) {
            return $this->ok([
                'id' => $botId,
                'is_bot' => true,
                'first_name' => $bot['name'] ?: 'Bot',
                'last_name' => null,
                'username' => $bot['username'] ?: 'bot',
                'language_code' => 'en',
                'is_premium' => false,
                'added_to_attachment_menu' => false,
                'can_join_groups' => (bool) $bot['can_join_groups'],
                'can_read_all_group_messages' => (bool) $bot['can_read_all_group_messages'],
                'supports_inline_queries' => (bool) $bot['supports_inline_queries'],
                'can_connect_to_business' => (bool) $bot['can_connect_to_business'],
                'has_main_web_app' => (bool) $bot['has_main_web_app'],
            ]);
        }

        return $this->ok([
            'id' => $botId,
            'is_bot' => true,
            'first_name' => 'Bot',
            'last_name' => null,
            'username' => 'bot',
            'language_code' => 'en',
            'is_premium' => false,
            'added_to_attachment_menu' => false,
            'can_join_groups' => true,
            'can_read_all_group_messages' => false,
            'supports_inline_queries' => false,
            'can_connect_to_business' => false,
            'has_main_web_app' => false,
        ]);
    }

    /**
     * logOut — Log out from cloud Bot API server
     */
    public function logOut(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);

        // Invalidate bot sessions
        $this->db->table('sessions')
            ->where('user_id', $botId)
            ->delete();

        $this->db->table('bot_accounts')
            ->where('token', $token)
            ->update(['is_active' => false]);

        return $this->ok(true);
    }

    /**
     * close — Close the bot instance
     */
    public function close(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);

        $this->db->table('bot_accounts')
            ->where('token', $token)
            ->update(['is_active' => false]);

        // Clear all webhooks for this bot
        $this->db->table('webhooks')
            ->where('user_id', $botId)
            ->delete();

        return $this->ok(true);
    }

    /**
     * getMyName — Get bot name
     */
    public function getMyName(Request $request, string $token): Response
    {
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        return $this->ok(['name' => $bot['name'] ?? 'Bot']);
    }

    /**
     * setMyName — Set bot name
     */
    public function setMyName(Request $request, string $token): Response
    {
        try {
            $name = $this->required($request, 'name');

            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if ($existing) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['name' => $name]);
            } else {
                $this->db->table('bot_accounts')->insert([
                    'user_id' => $this->getBotId($token),
                    'token' => $token,
                    'name' => $name,
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getMyDescription — Get bot description
     */
    public function getMyDescription(Request $request, string $token): Response
    {
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        return $this->ok(['description' => $bot['description'] ?? '']);
    }

    /**
     * setMyDescription — Set bot description
     */
    public function setMyDescription(Request $request, string $token): Response
    {
        try {
            $description = $this->required($request, 'description');

            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if ($existing) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['description' => $description]);
            } else {
                $this->db->table('bot_accounts')->insert([
                    'user_id' => $this->getBotId($token),
                    'token' => $token,
                    'description' => $description,
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getMyShortDescription — Get bot short description
     */
    public function getMyShortDescription(Request $request, string $token): Response
    {
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        return $this->ok(['short_description' => $bot['short_description'] ?? '']);
    }

    /**
     * setMyShortDescription — Set bot short description
     */
    public function setMyShortDescription(Request $request, string $token): Response
    {
        try {
            $shortDescription = $this->required($request, 'short_description');

            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if ($existing) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['short_description' => $shortDescription]);
            } else {
                $this->db->table('bot_accounts')->insert([
                    'user_id' => $this->getBotId($token),
                    'token' => $token,
                    'short_description' => $shortDescription,
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getMyCommands — Get bot commands list
     */
    public function getMyCommands(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);
        $commands = $this->db->table('bot_commands')
            ->where('bot_id', $botId)
            ->get();

        return $this->ok(array_map(fn($c) => [
            'command' => $c['command'],
            'description' => $c['description'],
        ], $commands));
    }

    /**
     * setMyCommands — Set bot commands
     */
    public function setMyCommands(Request $request, string $token): Response
    {
        try {
            $commandsRaw = $this->required($request, 'commands');
            $commands = is_string($commandsRaw) ? json_decode($commandsRaw, true) : $commandsRaw;
            $botId = $this->getBotId($token);

            $this->db->table('bot_commands')
                ->where('bot_id', $botId)
                ->delete();

            foreach ($commands as $cmd) {
                $this->db->table('bot_commands')->insert([
                    'bot_id' => $botId,
                    'command' => $cmd['command'],
                    'description' => $cmd['description'] ?? '',
                    'scope_type' => $this->input($request, 'scope', 'default'),
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteMyCommands — Delete bot commands
     */
    public function deleteMyCommands(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);
        $this->db->table('bot_commands')
            ->where('bot_id', $botId)
            ->delete();
        return $this->ok(true);
    }

    /**
     * getMyDefaultAdministratorRights — Get default admin rights
     */
    public function getMyDefaultAdministratorRights(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);
        $bot = $this->db->table('bot_accounts')
            ->where('token', $token)
            ->first();

        if ($bot && $bot['default_admin_rights'] ?? null) {
            return $this->ok(json_decode($bot['default_admin_rights'], true));
        }

        return $this->ok(null);
    }

    /**
     * setMyDefaultAdministratorRights — Set default admin rights
     */
    public function setMyDefaultAdministratorRights(Request $request, string $token): Response
    {
        try {
            $rightsRaw = $this->required($request, 'rights');
            $rights = is_string($rightsRaw) ? json_decode($rightsRaw, true) : $rightsRaw;

            $existing = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if ($existing) {
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['default_admin_rights' => json_encode($rights)]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getMyDefaultChatAdministratorRights — Get default chat administrator rights
     */
    public function getMyDefaultChatAdministratorRights(Request $request, string $token): Response
    {
        try {
            $bot = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if (!$bot) {
                return $this->error('Bot not found', 404);
            }

            $defaultRights = json_decode($bot['default_chat_admin_rights'] ?? 'true', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $defaultRights = true; // fallback to enabled if invalid JSON
            }

            // Ensure we return proper boolean values for all expected fields
            $defaults = [
                'can_change_info' => true,
                'can_post_messages' => true,
                'can_edit_messages' => true,
                'can_delete_messages' => true,
                'can_post_stories' => true,
                'can_edit_stories' => true,
                'can_delete_stories' => true,
                'can_invite_users' => true,
                'can_manage_topics' => true,
                'can_pin_messages' => true,
                'can_manage_video_chats' => true,
                'can_restrict_members' => true,
                'can_promote_members' => true,
                'can_manage_chat' => true,
            ];

            // Merge with stored rights, keeping defaults for missing keys
            $result = array_merge($defaults, array_intersect_key($defaultRights, $defaults));

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setMyDefaultChatAdministratorRights — Set default chat administrator rights
     */
    public function setMyDefaultChatAdministratorRights(Request $request, string $token): Response
    {
        try {
            $rights = $this->input($request, 'rights');
            $isNull = $this->input($request, 'rights') === null;

            $bot = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if (!$bot) {
                return $this->error('Bot not found', 404);
            }

            if ($isNull) {
                // Reset to default (all true)
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['default_chat_admin_rights' => null]);
            } else {
                // Validate that rights is an object
                if (!is_array($rights)) {
                    return $this->error('Rights must be a JSON object', 400);
                }

                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['default_chat_admin_rights' => json_encode($rights)]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getMyDefaultChatPermissions — Get default chat permissions
     */
    public function getMyDefaultChatPermissions(Request $request, string $token): Response
    {
        try {
            $bot = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if (!$bot) {
                return $this->error('Bot not found', 404);
            }

            $defaultPermissions = json_decode($bot['default_chat_permissions'] ?? 'true', true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $defaultPermissions = true; // fallback to enabled if invalid JSON
            }

            // Ensure we return proper boolean values for all expected fields
            $defaults = [
                'can_send_messages' => true,
                'can_send_audios' => true,
                'can_send_documents' => true,
                'can_send_photos' => true,
                'can_send_videos' => true,
                'can_send_video_notes' => true,
                'can_send_voice_notes' => true,
                'can_send_polls' => true,
                'can_send_other_messages' => true,
                'can_add_web_page_previews' => true,
                'can_change_info' => true,
                'can_invite_users' => true,
                'can_pin_messages' => true,
                'can_manage_topics' => true,
            ];

            // Merge with stored permissions, keeping defaults for missing keys
            $result = array_merge($defaults, array_intersect_key($defaultPermissions, $defaults));

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setMyDefaultChatPermissions — Set default chat permissions
     */
    public function setMyDefaultChatPermissions(Request $request, string $token): Response
    {
        try {
            $permissions = $this->input($request, 'permissions');
            $isNull = $this->input($request, 'permissions') === null;

            $bot = $this->db->table('bot_accounts')
                ->where('token', $token)
                ->first();

            if (!$bot) {
                return $this->error('Bot not found', 404);
            }

            if ($isNull) {
                // Reset to default (all true)
                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['default_chat_permissions' => null]);
            } else {
                // Validate that permissions is an object
                if (!is_array($permissions)) {
                    return $this->error('Permissions must be a JSON object', 400);
                }

                $this->db->table('bot_accounts')
                    ->where('token', $token)
                    ->update(['default_chat_permissions' => json_encode($permissions)]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Get bot ID from token
     */
    private function getBotId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
