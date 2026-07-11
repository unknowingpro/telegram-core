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
            $score = $this->required($request, 'score');
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
        return $this->ok([]);
    }

    private function generateMessageId(): int
    {
        return (int) (microtime(true) * 1000) + random_int(0, 999);
    }
}
