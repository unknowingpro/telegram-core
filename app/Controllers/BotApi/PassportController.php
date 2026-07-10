<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Passport controller — setPassportDataErrors
 */
class PassportController extends BaseController
{
    public function setPassportDataErrors(Request $request, string $token): Response
    {
        return $this->ok(true);
    }
}
