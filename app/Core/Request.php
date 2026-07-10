<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Typed request wrapper
 * Extracts method, URI, headers, body from PHP superglobals
 */
class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $query;
    private array $body;
    private array $server;
    private ?array $jsonBody = null;

    public function __construct(
        string $method,
        string $uri,
        array $headers = [],
        array $query = [],
        array $body = [],
        array $server = []
    ) {
        $this->method = strtoupper($method);
        $this->uri = $uri;
        $this->headers = $this->normalizeHeaders($headers);
        $this->query = $query;
        $this->body = $body;
        $this->server = $server;
    }

    /**
     * Capture the current PHP request
     */
    public static function capture(): self
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri = rawurldecode($uri);

        // Normalize headers
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $header = strtolower(str_replace('_', '-', substr($key, 5)));
                $headers[$header] = $value;
            }
        }

        $query = $_GET;
        $body = $_POST;

        // Parse JSON body
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json')) {
            $raw = file_get_contents('php://input');
            $body = json_decode($raw, true) ?? [];
        }

        // Parse multipart/form-data
        if (str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data')) {
            $body = array_merge($_POST, $_FILES);
        }

        return new self($method, $uri, $headers, $query, $body, $_SERVER);
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $key => $value) {
            $normalized[strtolower($key)] = $value;
        }
        return $normalized;
    }

    // Getters
    public function method(): string { return $this->method; }
    public function uri(): string { return $this->uri; }
    public function path(): string { return $this->uri; }
    public function query(): array { return $this->query; }
    public function server(): array { return $this->server; }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    public function headers(): array { return $this->headers; }

    /**
     * Get body parameter (POST or JSON)
     */
    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $this->query[$key] ?? $default;
    }

    /**
     * Get all input data
     */
    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    /**
     * Get JSON body
     */
    public function json(): array
    {
        if ($this->jsonBody === null) {
            $raw = file_get_contents('php://input');
            $this->jsonBody = json_decode($raw, true) ?? [];
        }
        return $this->jsonBody;
    }

    /**
     * Get a specific input with type casting
     */
    public function int(string $key, int $default = 0): int
    {
        return (int) $this->input($key, $default);
    }

    public function string(string $key, string $default = ''): string
    {
        return (string) $this->input($key, $default);
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->input($key, $default);
        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }
        return (bool) $value;
    }

    /**
     * Get Authorization header (Bearer token)
     */
    public function bearerToken(): ?string
    {
        $auth = $this->header('authorization');
        if ($auth && preg_match('/Bearer\s+(.+)$/i', $auth, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Get client IP address
     */
    public function ip(): string
    {
        return $this->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get user agent
     */
    public function userAgent(): string
    {
        return $this->header('user-agent') ?? '';
    }
}
