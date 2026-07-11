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
        return $this->ok([
            'id' => $this->getBotId($token),
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
        return $this->ok(true);
    }

    /**
     * close — Close the bot instance
     */
    public function close(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * getMyName — Get bot name
     */
    public function getMyName(Request $request, string $token): Response
    {
        return $this->ok(['name' => 'Bot']);
    }

    /**
     * setMyName — Set bot name
     */
    public function setMyName(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * getMyDescription — Get bot description
     */
    public function getMyDescription(Request $request, string $token): Response
    {
        return $this->ok(['description' => '']);
    }

    /**
     * setMyDescription — Set bot description
     */
    public function setMyDescription(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * getMyShortDescription — Get bot short description
     */
    public function getMyShortDescription(Request $request, string $token): Response
    {
        return $this->ok(['short_description' => '']);
    }

    /**
     * setMyShortDescription — Set bot short description
     */
    public function setMyShortDescription(Request $request, string $token): Response
    {
        return $this->ok(true);
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

            // Clear existing commands for this scope
            $this->db->table('bot_commands')
                ->where('bot_id', $botId)
                ->delete();

            // Insert new commands
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
        return $this->ok(null);
    }

    /**
     * setMyDefaultAdministratorRights — Set default admin rights
     */
    public function setMyDefaultAdministratorRights(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    /**
     * Get bot ID from token
     */
    private function getBotId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
