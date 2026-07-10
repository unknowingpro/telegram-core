<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Health check controller
 */
class HealthController extends BaseController
{
    /**
     * GET /api/health
     */
    public function check(Request $request): Response
    {
        return $this->ok([
            'status' => 'ok',
            'timestamp' => now(),
            'php_version' => PHP_VERSION,
        ]);
    }
}
