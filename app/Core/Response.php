<?php
declare(strict_types=1);

namespace App\Core;

/**
 * JSON response helper
 * Envelopes all responses in { ok, data, error, meta } format
 */
class Response
{
    private int $statusCode;
    private array $headers;
    private mixed $body;

    public function __construct(int $statusCode = 200, mixed $body = null, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = array_merge([
            'Content-Type' => 'application/json',
        ], $headers);
    }

    /**
     * Success response: { ok: true, result: ... }
     * Mirrors Telegram Bot API exact response format
     */
    public static function ok(mixed $data = null, array $meta = []): self
    {
        $response = [
            'ok' => true,
            'result' => $data,
        ];
        if ($meta) {
            $response['meta'] = $meta;
        }
        return new self(200, $response);
    }

    /**
     * Error response: { ok: false, error_code: ..., description: "..." }
     * Mirrors Telegram Bot API exact error format
     */
    public static function error(string $description, int $errorCode = 400, ?array $parameters = null): self
    {
        $error = [
            'ok' => false,
            'error_code' => $errorCode,
            'description' => $description,
        ];
        if ($parameters !== null) {
            $error['parameters'] = $parameters;
        }
        return new self($errorCode, $error);
    }

    /**
     * Send the response
     */
    public function send(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        if (is_array($this->body)) {
            echo json_encode($this->body, JSON_UNESCAPED_UNICODE);
        } else {
            echo $this->body;
        }
    }

    public function statusCode(): int { return $this->statusCode; }
    public function body(): mixed { return $this->body; }
}
