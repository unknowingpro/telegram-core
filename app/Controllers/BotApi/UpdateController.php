<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\UpdateModel;

/**
 * Update controller — handles getUpdates, setWebhook, deleteWebhook, getWebhookInfo
 * Mirrors Telegram Bot API update methods exactly
 */
class UpdateController extends BaseController
{
    private UpdateModel $updateModel;

    public function __construct()
    {
        parent::__construct();
        $this->updateModel = new UpdateModel();
    }

    /**
     * getUpdates — Long polling to receive updates
     *
     * When timeout > 0, blocks for up to that many seconds waiting for new updates.
     * Uses short sleep-poll cycles to avoid blocking DB connections.
     */
    public function getUpdates(Request $request, string $token): Response
    {
        $userId = $this->getBotId($token);
        $offset = $this->intInput($request, 'offset', 0);
        $limit = min($this->intInput($request, 'limit', 100), 100);
        $timeout = $this->intInput($request, 'timeout', 0);
        $allowedUpdates = $this->input($request, 'allowed_updates');

        // Parse allowed_updates JSON
        if (is_string($allowedUpdates)) {
            $allowedUpdates = json_decode($allowedUpdates, true);
        }

        // Clamp timeout to prevent excessive blocking (0-60 seconds)
        $timeout = max(0, min($timeout, 60));

        // Long-poll: if timeout > 0 and no updates yet, sleep-poll until
        // we have data or the timeout expires
        $maxWait = time() + $timeout;
        $polled = false;

        do {
            // Get updates from queue
            $updates = $this->updateModel->getUpdates($userId, $offset, $limit, $allowedUpdates);

            if (!empty($updates)) {
                // Found updates — return immediately
                $result = array_map([$this->updateModel, 'toTelegram'], $updates);

                // Mark as read up to the highest update ID returned
                $lastId = max(array_column($updates, 'id'));
                $this->updateModel->markAsRead($userId, (int) $lastId);

                return $this->ok($result);
            }

            if ($timeout > 0 && time() < $maxWait) {
                // No updates yet and we have time — sleep briefly then poll again
                $polled = true;
                usleep(250_000); // 250ms between polls
            } else {
                break;
            }
        } while (true);

        // No updates available (or timeout expired) — return empty array
        return $this->ok([]);
    }

    /**
     * setWebhook — Set webhook URL
     */
    public function setWebhook(Request $request, string $token): Response
    {
        try {
            $url = $this->required($request, 'url');
            $secretToken = $this->input($request, 'secret_token');
            $allowedUpdates = $this->input($request, 'allowed_updates');
            $maxConnections = $this->intInput($request, 'max_connections', 40);
            $ipAddress = $this->input($request, 'ip_address');
            $dropPendingUpdates = $this->boolInput($request, 'drop_pending_updates');
            $hasCustomCertificate = $this->boolInput($request, 'certificate') || $this->input($request, 'certificate') !== null;

            $botId = $this->getBotId($token);

            // Delete existing webhook if drop_pending_updates
            if ($dropPendingUpdates) {
                $this->db->table('webhooks')
                    ->where('user_id', $botId)
                    ->delete();
            }

            // Upsert webhook
            $existing = $this->db->table('webhooks')
                ->where('user_id', $botId)
                ->first();

            $webhookData = [
                'url' => $url,
                'secret_token' => $secretToken,
                'allowed_updates' => is_string($allowedUpdates) ? $allowedUpdates : json_encode($allowedUpdates),
                'has_custom_certificate' => $hasCustomCertificate,
                'max_connections' => $maxConnections,
                'ip_address' => $ipAddress,
            ];

            if ($existing) {
                $this->db->table('webhooks')
                    ->where('user_id', $botId)
                    ->update($webhookData);
            } else {
                $webhookData['user_id'] = $botId;
                $webhookData['pending_update_count'] = 0;
                $this->db->table('webhooks')->insert($webhookData);
            }

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * deleteWebhook — Remove webhook
     */
    public function deleteWebhook(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);
        $dropPending = $this->boolInput($request, 'drop_pending_updates');

        $this->db->table('webhooks')
            ->where('user_id', $botId)
            ->delete();

        if ($dropPending) {
            $this->db->table('updates')
                ->where('user_id', $botId)
                ->delete();
        }

        return $this->ok(true);
    }

    /**
     * getWebhookInfo — Get current webhook config
     */
    public function getWebhookInfo(Request $request, string $token): Response
    {
        $botId = $this->getBotId($token);

        $webhook = $this->db->table('webhooks')
            ->where('user_id', $botId)
            ->first();

        if (!$webhook) {
            return $this->ok([
                'url' => '',
                'has_custom_certificate' => false,
                'pending_update_count' => 0,
                'last_error_date' => null,
                'last_error_message' => null,
                'last_synchronization_error_date' => null,
                'max_connections' => 40,
                'ip_address' => null,
            ]);
        }

        return $this->ok([
            'url' => $webhook['url'],
            'has_custom_certificate' => (bool) $webhook['has_custom_certificate'],
            'pending_update_count' => (int) ($webhook['pending_update_count'] ?? 0),
            'last_error_date' => $webhook['last_error_date'] ?? null,
            'last_error_message' => $webhook['last_error'] ?? null,
            'last_synchronization_error_date' => null,
            'max_connections' => (int) ($webhook['max_connections'] ?? 40),
            'ip_address' => $webhook['ip_address'] ?? null,
        ]);
    }

    }
