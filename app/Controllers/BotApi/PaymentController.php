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

            $linkHash = substr(md5($payload . time()), 0, 16);
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

            return $this->ok(true);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }

    /**
     * getStarTransactions — Get star transactions
     */
    public function getStarTransactions(Request $request, string $token): Response
    {
        return $this->ok(['transactions' => []]);
    }

    /**
     * refundStarPayment — Refund star payment
     */
    public function refundStarPayment(Request $request, string $token): Response
    {
        return $this->ok(true);
    }

    private function generateMessageId(): int
    {
        return (int) (microtime(true) * 1000) + random_int(0, 999);
    }
}
