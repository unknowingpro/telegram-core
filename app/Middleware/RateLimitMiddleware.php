<?php
declare(strict_types=1);

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;

/**
 * Rate limit middleware — IP-based request throttling
 * Uses file-backed storage (storage/data/rate_limits.json)
 */
class RateLimitMiddleware
{
    private int $maxRequests;
    private int $windowSeconds;
    private string $storagePath;

    public function __construct()
    {
        $this->maxRequests = (int) env('RATE_LIMIT_MAX', 60);
        $this->windowSeconds = (int) env('RATE_LIMIT_WINDOW', 60);
        $this->storagePath = BASE_PATH . '/storage/data/rate_limits.json';
    }

    /**
     * Handle the request
     */
    public function handle(Request $request): ?Response
    {
        $ip = $request->ip();
        $key = $this->getKey($ip);
        $now = time();

        // Load rate limit data
        $data = $this->loadData();

        // Clean expired entries
        if (isset($data[$key])) {
            $data[$key] = array_filter($data[$key], function ($timestamp) use ($now) {
                return ($now - $timestamp) < $this->windowSeconds;
            });
        }

        $currentCount = count($data[$key] ?? []);

        if ($currentCount >= $this->maxRequests) {
            $retryAfter = $this->windowSeconds - ($now - min($data[$key]));
            return Response::error(
                'Rate limit exceeded',
                429,
                ['retry_after' => max(1, $retryAfter)]
            );
        }

        // Record this request
        $data[$key][] = $now;
        $this->saveData($data);

        return null; // Continue to controller
    }

    private function getKey(string $ip): string
    {
        return "rate_limit:{$ip}";
    }

    private function loadData(): array
    {
        if (!file_exists($this->storagePath)) {
            return [];
        }

        $content = file_get_contents($this->storagePath);
        return json_decode($content, true) ?? [];
    }

    private function saveData(array $data): void
    {
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->storagePath, json_encode($data), LOCK_EX);
    }
}
