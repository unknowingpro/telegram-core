<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\FileService;

/**
 * Base controller with common helpers
 * All controllers extend this
 */
abstract class BaseController
{
    protected Database $db;
    private ?FileService $fileService = null;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get bot ID from token
     */
    protected function getBotId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
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

    /**
     * Resolve an input file from the request.
     *
     * If the request field contains a PHP file upload array (multipart/form-data),
     * saves the file to disk via FileService and returns the generated file_id.
     * If it's already a string (file_id reference), returns it as-is.
     *
     * @param Request $request The incoming request
     * @param string $field The field name to check (e.g. 'photo', 'document')
     * @param int|string $userId Owner of the file
     * @param string|null $customFileIdPrefix Optional prefix (e.g. 'sticker_')
     * @return string|null The file_id string, or null if no file/upload found
     */
    protected function resolveFileUpload(Request $request, string $field, int|string $userId, ?string $customFileIdPrefix = null): ?string
    {
        $value = $request->input($field);

        // No input at all
        if ($value === null) {
            return null;
        }

        // It's a file upload array (multipart)
        if (is_array($value) && isset($value['tmp_name']) && $value['tmp_name'] !== '') {
            $this->fileService ??= new FileService();
            $result = $this->fileService->storeUpload($value, $userId, $customFileIdPrefix);
            return $result['file_id'];
        }

        // It's already a string (file_id or URL reference)
        if (is_string($value) && $value !== '') {
            // Check if it's an attach:// reference (Telegram InputFile attach protocol)
            if (str_starts_with($value, 'attach://')) {
                $attachField = substr($value, 9);
                $attachValue = $request->input($attachField);
                if (is_array($attachValue) && isset($attachValue['tmp_name']) && $attachValue['tmp_name'] !== '') {
                    $this->fileService ??= new FileService();
                    $result = $this->fileService->storeUpload($attachValue, $userId, $customFileIdPrefix);
                    return $result['file_id'];
                }
            }
            return $value;
        }

        return null;
    }
}
