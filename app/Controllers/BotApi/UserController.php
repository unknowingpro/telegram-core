<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * User controller — profile photos, verification, emoji status
 */
class UserController extends BaseController
{
    public function getUserProfilePhotos(Request $request, string $token): Response { return $this->ok(['total_count' => 0, 'photos' => []]); }
    public function getUserProfileAudios(Request $request, string $token): Response { return $this->ok(['total_count' => 0, 'audios' => []]); }
    public function setUserEmojiStatus(Request $request, string $token): Response { return $this->ok(true); }
    public function removeUserVerification(Request $request, string $token): Response { return $this->ok(true); }
    public function removeChatVerification(Request $request, string $token): Response { return $this->ok(true); }
    public function verifyUser(Request $request, string $token): Response { return $this->ok(true); }
    public function verifyChat(Request $request, string $token): Response { return $this->ok(true); }
    public function setMyProfilePhoto(Request $request, string $token): Response { return $this->ok(true); }
    public function removeMyProfilePhoto(Request $request, string $token): Response { return $this->ok(true); }
}
