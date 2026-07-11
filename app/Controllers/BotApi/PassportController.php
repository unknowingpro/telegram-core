<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Passport controller — setPassportDataErrors
 * Mirrors Telegram Bot API passport methods exactly
 */
class PassportController extends BaseController
{
    /**
     * setPassportDataErrors — Set passport data errors
     *
     * Stores passport errors in the database for audit and verification.
     * Telegram expects this to be called when user-submitted passport data
     * fails validation — errors are presented to the user for correction.
     */
    public function setPassportDataErrors(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $errorsRaw = $this->required($request, 'errors');
            $errors = is_string($errorsRaw) ? json_decode($errorsRaw, true) : $errorsRaw;

            if (!is_array($errors) || empty($errors)) {
                return $this->error('errors must be a non-empty array', 400);
            }

            // Store each passport error for audit
            foreach ($errors as $error) {
                $this->db->table('passport_errors')->insert([
                    'user_id' => $userId,
                    'type' => $error['type'] ?? 'unknown',
                    'source' => $error['source'] ?? null,
                    'field_name' => $error['field_name'] ?? null,
                    'data_hash' => $error['data_hash'] ?? null,
                    'message' => json_encode($error),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
