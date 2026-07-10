<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

/**
 * Auth controller — handles user registration and login
 */
class AuthController extends BaseController
{
    private AuthService $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthService();
    }

    /**
     * POST /api/auth/register
     * Body: phone, password, first_name, [last_name], [username], [language_code]
     */
    public function register(Request $request): Response
    {
        try {
            $result = $this->auth->register([
                'phone' => $this->required($request, 'phone'),
                'password' => $this->required($request, 'password'),
                'first_name' => $this->required($request, 'first_name'),
                'last_name' => $this->input($request, 'last_name'),
                'username' => $this->input($request, 'username'),
                'language_code' => $this->input($request, 'language_code', 'en'),
            ]);

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * POST /api/auth/login
     * Body: phone, password
     */
    public function login(Request $request): Response
    {
        try {
            $result = $this->auth->login(
                $this->required($request, 'phone'),
                $this->required($request, 'password'),
                $request->ip(),
                $request->userAgent()
            );

            return $this->ok($result);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 401);
        }
    }
}
