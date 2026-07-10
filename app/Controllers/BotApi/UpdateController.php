<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\UpdateModel;

/**
 * Update controller — handles getUpdates, setWebhook, deleteWebhook, getWebhookInfo
 * Mirrors Telegram Bot API update methods
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
     */
    public function getUpdates(Request $request, string $token): Response
    {
        $userId = $this->getBotUserId($token);
        $offset = $this->intInput($request, 'offset', 0);
        $limit = min($this->intInput($request, 'limit', 100), 100);
        $timeout = $this->intInput($request, 'timeout', 0);
        $allowedUpdates = $this->input($request, 'allowed_updates');

        // Parse allowed_updates JSON
        if (is_string($allowedUpdates)) {
            $allowedUpdates = json_decode($allowedUpdates, true);
        }

        // Get updates from queue
        $updates = $this->updateModel->getUpdates($userId, $offset, $limit, $allowedUpdates);

        // Convert to Telegram format
        $result = array_map([$this->updateModel, 'toTelegram'], $updates);

        return $this->ok($result);
    }

    /**
     * setWebhook — Set webhook URL
     */
    public function setWebhook(Request $request, string $token): Response
    {
        $url = $this->required($request, 'url');
        $secretToken = $this->input($request, 'secret_token');
        $allowedUpdates = $this->input($request, 'allowed_updates');

        // TODO: Store webhook config in database
        // TODO: Validate URL is reachable

        return $this->ok(true);
    }

    /**
     * deleteWebhook — Remove webhook
     */
    public function deleteWebhook(Request $request, string $token): Response
    {
        // TODO: Remove webhook from database
        return $this->ok(true);
    }

    /**
     * getWebhookInfo — Get current webhook config
     */
    public function getWebhookInfo(Request $request, string $token): Response
    {
        return $this->ok([
            'url' => '',
            'has_custom_certificate' => false,
            'pending_update_count' => 0,
            'last_error_date' => null,
            'last_error_message' => null,
        ]);
    }

    private function getBotUserId(string $token): int
    {
        return (int) hexdec(substr(hash('sha256', $token), 0, 15));
    }
}
