<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Forum controller — forum topic management
 */
class ForumController extends BaseController
{
    public function createForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function editForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function closeForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function reopenForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function unpinAllForumTopicMessages(Request $request, string $token): Response { return $this->ok(true); }
    public function getForumTopicIconStickers(Request $request, string $token): Response { return $this->ok([]); }
    public function hideGeneralForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function unhideGeneralForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function editGeneralForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function closeGeneralForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function reopenGeneralForumTopic(Request $request, string $token): Response { return $this->ok(true); }
    public function unpinAllGeneralForumTopicMessages(Request $request, string $token): Response { return $this->ok(true); }
}
