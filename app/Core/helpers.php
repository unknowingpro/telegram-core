<?php
declare(strict_types=1);

use App\Core\App;
use App\Core\Response;

if (!function_exists('env')) {
    /**
     * Get environment variable with default
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }

        // Auto-cast booleans
        if (in_array(strtolower($value), ['true', '1', 'yes'], true)) {
            return true;
        }
        if (in_array(strtolower($value), ['false', '0', 'no'], true)) {
            return false;
        }

        return $value;
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and die — debug helper
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre>' . print_r($var, true) . '</pre>';
        }
        exit(1);
    }
}

if (!function_exists('abort')) {
    /**
     * Send error response and exit
     */
    function abort(string $message, int $code = 400): never
    {
        Response::error($message, $code)->send();
        exit;
    }
}

if (!function_exists('json_response')) {
    /**
     * Quick JSON response
     */
    function json_response(mixed $data, int $code = 200): Response
    {
        return new Response($code, $data);
    }
}

if (!function_exists('now')) {
    /**
     * Current timestamp in UTC
     */
    function now(): string
    {
        return gmdate('Y-m-d H:i:s');
    }
}

if (!function_exists('generate_token')) {
    /**
     * Generate a secure random token
     */
    function generate_token(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
}

if (!function_exists('slugify')) {
    /**
     * Create a URL-safe slug
     */
    function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        return strtolower($text);
    }
}
