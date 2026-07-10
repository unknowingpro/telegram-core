<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Bot settings controller — getMe, getMyName, setMyCommands, etc.
 */
class BotSettingsController extends BaseController
{
    /**
     * getMe — Get basic bot info
     */
    public function getMe(Request $request, string $token): Response
    {
        // TODO: Look up bot in database by token
        return $this->ok([
            'id' => (int) hexdec(substr(hash('sha256', $token), 0, 15)),
            'is_bot' => true,
            'first_name' => 'MyBot',
            'username' => 'mybot',
            'can_join_groups' => true,
            'can_read_all_group_messages' => false,
            'supports_inline_queries' => false,
        ]);
    }

    public function logOut(Request $request, string $token): Response { return $this->ok(true); }
    public function close(Request $request, string $token): Response { return $this->ok(true); }

    public function getMyName(Request $request, string $token): Response
    {
        return $this->ok(['name' => 'MyBot']);
    }

    public function setMyName(Request $request, string $token): Response { return $this->ok(true); }

    public function getMyDescription(Request $request, string $token): Response
    {
        return $this->ok(['description' => '']);
    }

    public function setMyDescription(Request $request, string $token): Response { return $this->ok(true); }
    public function getMyShortDescription(Request $request, string $token): Response { return $this->ok(['short_description' => '']); }
    public function setMyShortDescription(Request $request, string $token): Response { return $this->ok(true); }

    public function getMyCommands(Request $request, string $token): Response
    {
        return $this->ok([]);
    }

    public function setMyCommands(Request $request, string $token): Response { return $this->ok(true); }
    public function deleteMyCommands(Request $request, string $token): Response { return $this->ok(true); }
    public function getMyDefaultAdministratorRights(Request $request, string $token): Response { return $this->ok(null); }
    public function setMyDefaultAdministratorRights(Request $request, string $token): Response { return $this->ok(true); }
}
