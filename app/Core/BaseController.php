<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Base controller with common helpers
 * All controllers extend this
 */
abstract class BaseController
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Send a success JSON response
     */
    protected function ok(mixed $data = null, array $meta = []): Response
    {
        return Response::ok($data, $meta);
    }

    /**
     * Send an error JSON response
     */
    protected function error(string $message, int $code = 400, ?array $details = null): Response
    {
        return Response::error($message, $code, $details);
    }

    /**
     * Get a required input parameter
     */
    protected function required(Request $request, string $key): mixed
    {
        $value = $request->input($key);
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException("Required parameter: {$key}");
        }
        return $value;
    }

    /**
     * Get input with default
     */
    protected function input(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->input($key, $default);
    }

    /**
     * Extract typed input
     */
    protected function intInput(Request $request, string $key, int $default = 0): int
    {
        return $request->int($key, $default);
    }

    protected function stringInput(Request $request, string $key, string $default = ''): string
    {
        return $request->string($key, $default);
    }

    protected function boolInput(Request $request, string $key, bool $default = false): bool
    {
        return $request->bool($key, $default);
    }
}
