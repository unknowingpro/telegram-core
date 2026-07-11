<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Stories controller — story posting and management
 * Mirrors Telegram Bot API story methods
 */
class StoriesController extends BaseController
{
    public function postStory(Request $request, string $token): Response
    {
        return $this->ok([
            'id' => $this->generateStoryId(),
            'date' => time(),
            'expire_date' => time() + 86400,
        ]);
    }

    public function editStory(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function deleteStory(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function repostStory(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    public function getStoryStatistics(Request $request, string $token): Response
    {
        return $this->ok(['views' => 0, 'reactions' => 0]);
    }

    public function getUserStories(Request $request, string $token): Response
    {
        return $this->ok(['stories' => []]);
    }

    private function generateStoryId(): int
    {
        return (int) (microtime(true) * 1000) + random_int(0, 999);
    }
}
