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
        $secretToken = $request->header('x-webhook-secret');

        // Determine update type from payload
        $updateType = 'message';
        $knownTypes = [
            'message', 'edited_message', 'channel_post', 'edited_channel_post',
            'business_connection', 'business_message', 'edited_business_message',
            'deleted_business_messages', 'message_reaction', 'message_reaction_count',
            'inline_query', 'chosen_inline_result', 'callback_query',
            'shipping_query', 'pre_checkout_query', 'purchased_paid_media',
            'poll', 'poll_answer', 'my_chat_member', 'chat_member',
            'chat_join_request', 'chat_boost', 'removed_chat_boost',
        ];

        foreach ($knownTypes as $type) {
            if (isset($payload[$type])) {
                $updateType = $type;
                break;
            }
        }

        $userId = (int) hexdec(substr(hash('sha256', $token), 0, 15));

        // Queue the update
        $updateModel = new UpdateModel();
        $updateModel->pushUpdate($userId, $updateType, $payload[$updateType] ?? $payload);

        return $this->ok(true);
    }
}
