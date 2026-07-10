<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;
use App\Models\UpdateModel;

/**
 * Webhook controller — receives incoming updates from clients
 * This is the POST endpoint that clients push updates to
 */
class WebhookController extends BaseController
{
    /**
     * POST /webhook/{token}
     * Receives an update payload and queues it for long polling
     */
    public function handle(Request $request, string $token): Response
    {
        $payload = $request->json();

        if (empty($payload)) {
            return $this->error('Invalid JSON payload', 400);
        }

        // Verify webhook secret token (if configured)
        $secretToken = $request->header('X-Webhook-Secret');
        // TODO: Verify against stored secret for this token

        // Determine update type from payload
        $updateType = 'message'; // Default
        $knownTypes = [
            'message', 'edited_message', 'channel_post', 'callback_query',
            'inline_query', 'poll', 'poll_answer', 'chat_member',
        ];

        foreach ($knownTypes as $type) {
            if (isset($payload[$type])) {
                $updateType = $type;
                break;
            }
        }

        // Find the bot user by token
        // TODO: Look up bot in database, get owner user_id
        $userId = (int) hexdec(substr(hash('sha256', $token), 0, 15));

        // Queue the update
        $updateModel = new UpdateModel();
        $updateModel->pushUpdate($userId, $updateType, $payload[$updateType] ?? $payload);

        return $this->ok(true);
    }
}
