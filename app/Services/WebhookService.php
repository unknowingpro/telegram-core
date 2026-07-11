<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * Webhook service — forwards updates from the queue to bot webhook URLs
 * Runs as a background worker (CLI mode)
 */
class WebhookService
{
    private Database $db;
    private int $maxConcurrent = 40;
    private int $pollInterval = 1; // seconds between polls
    private bool $running = true;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Start the webhook processing loop
     * Usage: php bin/webhook-worker.php
     */
    public function run(): void
    {
        $this->log('Webhook worker started');

        while ($this->running) {
            try {
                $this->processPendingUpdates();
                sleep($this->pollInterval);
            } catch (\Throwable $e) {
                $this->log('Error: ' . $e->getMessage());
                sleep(5);
            }
        }
    }

    /**
     * Process all pending updates for all bots with webhooks
     */
    private function processPendingUpdates(): void
    {
        // Get all bots with active webhooks
        $webhooks = $this->db->rawFetchAll(
            "SELECT w.*, w.user_id as bot_id
             FROM webhooks w
             WHERE w.url != ''
             ORDER BY w.id ASC"
        );

        foreach ($webhooks as $webhook) {
            $this->deliverUpdatesForBot($webhook);
        }
    }

    /**
     * Deliver pending updates for a specific bot
     */
    private function deliverUpdatesForBot(array $webhook): void
    {
        $botId = $webhook['bot_id'];

        // Get unprocessed updates for this bot
        $updates = $this->db->rawFetchAll(
            "SELECT u.* FROM updates u
             WHERE u.user_id = ? AND u.is_read = false
             ORDER BY u.id ASC
             LIMIT 100",
            [$botId]
        );

        if (empty($updates)) {
            return;
        }

        $allowedUpdates = $webhook['allowed_updates']
            ? json_decode($webhook['allowed_updates'], true)
            : null;

        foreach ($updates as $update) {
            // Filter by allowed updates if configured
            if ($allowedUpdates && !in_array($update['update_type'], $allowedUpdates)) {
                continue;
            }

            $success = $this->sendUpdate($webhook, $update);

            if ($success) {
                // Mark as delivered
                $this->db->raw(
                    "UPDATE updates SET is_read = true WHERE id = ?",
                    [$update['id']]
                );

                // Decrement pending counter
                $this->db->raw(
                    "UPDATE webhooks SET pending_update_count = GREATEST(0, pending_update_count - 1) WHERE id = ?",
                    [$webhook['id']]
                );
            } else {
                // Increment error counter, stop processing this bot on failure
                $this->db->raw(
                    "UPDATE webhooks SET
                     last_error = ?,
                     last_error_date = NOW(),
                     pending_update_count = pending_update_count + 1
                     WHERE id = ?",
                    ['Failed to deliver update #' . $update['id'], $webhook['id']]
                );
                break;
            }
        }
    }

    /**
     * Send a single update to the webhook URL
     */
    private function sendUpdate(array $webhook, array $update): bool
    {
        $payload = json_encode([
            'update_id' => (int) $update['id'],
            $update['update_type'] => json_decode($update['payload'], true),
        ]);

        $headers = [
            'Content-Type: application/json',
            'X-Telegram-Bot-Api-Secret-Token: ' . ($webhook['secret_token'] ?? ''),
        ];

        $ch = curl_init($webhook['url']);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FORBID_REUSE => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode < 200 || $httpCode >= 300) {
            $this->log("Delivery failed to {$webhook['url']}: HTTP {$httpCode} - " . ($error ?: $response));
            return false;
        }

        return true;
    }

    /**
     * Clean stale updates (older than 7 days)
     */
    public function cleanStaleUpdates(): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        return $this->db->raw(
            "DELETE FROM updates WHERE created_at < ?",
            [$cutoff]
        )->rowCount();
    }

    public function stop(): void
    {
        $this->running = false;
    }

    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        echo "[{$timestamp}] {$message}\n";
    }
}
