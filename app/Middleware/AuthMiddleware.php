<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

/**
 * Auth middleware — verifies Bearer token from Authorization header
 */
class AuthMiddleware
{
    /**
     * Handle the request — return Response to block, or null to continue
     *
     * For Bot API routes (path contains /bot/), auth is via the bot token
     * embedded in the URL path (Telegram-style), so we skip Bearer check.
     * For all other routes, a valid Bearer token is required.
     */
    public function handle(Request $request): ?Response
    {
        // Skip auth for Bot API routes — auth is via token in URL path
        if (str_contains($request->uri(), '/bot/')) {
            return null;
        }

        $token = $request->bearerToken();

        if (!$token) {
            return Response::error('Authorization token required', 401);
        }

        $auth = new AuthService();
        $user = $auth->validateToken($token);

        if (!$user) {
            return Response::error('Invalid or expired token', 401);
        }

        // Attach user to request (stored in $_REQUEST for controller access)
        $_REQUEST['_user'] = $user;

        return null; // Continue to controller
    }
}
