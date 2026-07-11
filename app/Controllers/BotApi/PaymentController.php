<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Payment controller — invoices, shipping, pre-checkout, stars
 * Mirrors Telegram Bot API payment methods exactly
 */
class PaymentController extends BaseController
{
    /**
     * sendInvoice — Send an invoice
     */
    public function sendInvoice(Request $request, string $token): Response
    {
        try {
            $chatId = $this->required($request, 'chat_id');
            $title = $this->required($request, 'title');
            $description = $this->required($request, 'description');
            $payload = $this->required($request, 'payload');
            $currency = $this->required($request, 'currency');
            $pricesRaw = $this->required($request, 'prices');
            $prices = is_string($pricesRaw) ? json_decode($pricesRaw, true) : $pricesRaw;

            $totalAmount = 0;
            foreach ($prices as $price) {
                $totalAmount += $price['amount'];
            }

            $botId = $this->getBotId($token);

            // Handle optional photo upload
            $photoUrl = $this->input($request, 'photo_url');
            $photoUpload = $this->resolveFileUpload($request, 'photo', $botId);

            $this->db->table('invoices')->insert([
                'user_id' => $botId,
                'title' => $title,
                'description' => $description,
                'payload' => $payload,
                'currency' => $currency,
                'total_amount' => $totalAmount,
                'prices' => json_encode($prices),
                'max_tip_amount' => $this->intInput($request, 'max_tip_amount'),
                'suggested_tip_amounts' => $this->input($request, 'suggested_tip_amounts'),
                'provider_token' => $this->input($request, 'provider_token'),
                'provider_data' => $this->input($request, 'provider_data'),
                'start_parameter' => $payload,
            ]);

            return $this->ok([
                'message_id' => $this->generateMessageId(),
                'date' => time(),
                'chat' => ['id' => (int) $chatId, 'type' => 'private'],
                'invoice' => [
                    'title' => $title,
                    'description' => $description,
                    'start_parameter' => $payload,
                    'currency' => $currency,
                    'total_amount' => $totalAmount,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * createInvoiceLink — Create invoice link
     */
    public function createInvoiceLink(Request $request, string $token): Response
    {
        try {
            $title = $this->required($request, 'title');
            $description = $this->required($request, 'description');
            $payload = $this->required($request, 'payload');
            $currency = $this->required($request, 'currency');
            $pricesRaw = $this->required($request, 'prices');
            $prices = is_string($pricesRaw) ? json_decode($pricesRaw, true) : $pricesRaw;

            $totalAmount = 0;
            foreach ($prices as $price) {
                $totalAmount += $price['amount'];
            }

            $botId = $this->getBotId($token);
            $linkHash = substr(md5($payload . time()), 0, 16);

            $this->db->table('invoices')->insert([
                'user_id' => $botId,
                'title' => $title,
                'description' => $description,
                'payload' => $payload,
                'currency' => $currency,
                'total_amount' => $totalAmount,
                'prices' => json_encode($prices),
                'max_tip_amount' => $this->intInput($request, 'max_tip_amount'),
                'suggested_tip_amounts' => $this->input($request, 'suggested_tip_amounts'),
                'provider_token' => $this->input($request, 'provider_token'),
                'provider_data' => $this->input($request, 'provider_data'),
                'start_parameter' => $linkHash,
            ]);

            return $this->ok("https://t.me/{$linkHash}/invoice");
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * answerShippingQuery — Answer shipping query
     */
    public function answerShippingQuery(Request $request, string $token): Response
    {
        try {
            $shippingQueryId = $this->required($request, 'shipping_query_id');
            $ok = $this->boolInput($request, 'ok');

            $this->db->table('callback_queries')->insert([
                'user_id' => $this->getBotId($token),
                'data' => json_encode([
                    'type' => 'shipping_query',
                    'shipping_query_id' => $shippingQueryId,
                    'ok' => $ok,
                    'shipping_options' => $this->input($request, 'shipping_options'),
                    'error_message' => $this->input($request, 'error_message'),
                ]),
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * answerPreCheckoutQuery — Answer pre-checkout query
     */
    public function answerPreCheckoutQuery(Request $request, string $token): Response
    {
        try {
            $preCheckoutQueryId = $this->required($request, 'pre_checkout_query_id');
            $ok = $this->boolInput($request, 'ok');

            $this->db->table('callback_queries')->insert([
                'user_id' => $this->getBotId($token),
                'data' => json_encode([
                    'type' => 'pre_checkout_query',
                    'pre_checkout_query_id' => $preCheckoutQueryId,
                    'ok' => $ok,
                    'error_message' => $this->input($request, 'error_message'),
                ]),
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * refundStarPayment — Refund star payment
     */
    public function refundStarPayment(Request $request, string $token): Response
    {
        try {
            $userId = $this->required($request, 'user_id');
            $telegramPaymentChargeId = $this->required($request, 'telegram_payment_charge_id');

            $this->db->table('star_transactions')->insert([
                'user_id' => $userId,
                'amount' => 0,
                'type' => 'refund',
                'description' => 'Refund for charge ' . $telegramPaymentChargeId,
            ]);

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    private function generateMessageId(): int
    {
        return (int) (microtime(true) * 1000) + random_int(0, 999);
    }

    }
