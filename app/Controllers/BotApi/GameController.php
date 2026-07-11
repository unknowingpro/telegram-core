<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Game controller — sendGame, setGameScore, getGameHighScores
 * Mirrors Telegram Bot API game methods exactly
 */
class GameController extends BaseController
{
    /**
     * sendGame — Send a game
     */
    public function sendGame(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $gameShortName = $this->required($request, 'game_short_name');

            return $this->ok([
                'message_id' => $this->generateMessageId(),
                'date' => time(),
                'chat' => ['id' => (int) $chatId, 'type' => 'private'],
                'game' => [
                    'title' => $gameShortName,
                    'description' => '',
                    'photo' => [],
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * setGameScore — Set game score
     */
    public function setGameScore(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $score = (int) $this->required($request, 'score');
            $force = $this->boolInput($request, 'force');
            $chatId = $this->input($request, 'chat_id');
            $messageId = $this->input($request, 'message_id');
            $gameShortName = $this->input($request, 'game_short_name', 'game');

            $existing = $this->db->table('game_scores')
                ->where('game_short_name', $gameShortName)
                ->where('user_id', $userId)
                ->where('chat_id', $chatId)
                ->first();

            if ($existing) {
                if ($force || $score > $existing['score']) {
                    $this->db->table('game_scores')
                        ->where('id', $existing['id'])
                        ->update([
                            'score' => $score,
                            'force' => $force ? 1 : 0,
                        ]);
                }
            } else {
                $this->db->table('game_scores')->insert([
                    'game_short_name' => $gameShortName,
                    'user_id' => $userId,
                    'chat_id' => $chatId ?: null,
                    'score' => $score,
                    'force' => $force ? 1 : 0,
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getGameHighScores — Get game high scores
     */
    public function getGameHighScores(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $chatId = $this->input($request, 'chat_id');
            $messageId = $this->input($request, 'message_id');
            $gameShortName = $this->input($request, 'game_short_name', 'game');

            $scores = $this->db->table('game_scores')
                ->where('game_short_name', $gameShortName)
                ->orderBy('score', 'DESC')
                ->limit(10)
                ->get();

            $result = array_map(fn($s) => [
                'position' => 0,
                'user' => [
                    'id' => (int) $s['user_id'],
                    'is_bot' => false,
                    'first_name' => 'Player',
                ],
                'score' => (int) $s['score'],
            ], $scores);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    private function generateMessageId(): int
    {
        return (int) (microtime(true) * 1000) + random_int(0, 999);
    }
}
