<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Game controller — sendGame, setGameScore, getGameHighScores
 */
class GameController extends BaseController
{
    public function sendGame(Request $request, string $token): Response { return $this->ok(true); }
    public function setGameScore(Request $request, string $token): Response { return $this->ok(true); }
    public function getGameHighScores(Request $request, string $token): Response { return $this->ok([]); }
}
