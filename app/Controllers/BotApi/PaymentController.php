<?php
declare(strict_types=1);

namespace App\Controllers\BotApi;

use App\Core\BaseController;
use App\Core\Request;
use App\Core\Response;

/**
 * Payment controller — invoices, shipping, pre-checkout, stars
 */
class PaymentController extends BaseController
{
    public function sendInvoice(Request $request, string $token): Response { return $this->ok(true); }
    public function createInvoiceLink(Request $request, string $token): Response { return $this->ok(true); }
    public function answerShippingQuery(Request $request, string $token): Response { return $this->ok(true); }
    public function answerPreCheckoutQuery(Request $request, string $token): Response { return $this->ok(true); }
    public function getStarTransactions(Request $request, string $token): Response { return $this->ok(['transactions' => []]); }
    public function refundStarPayment(Request $request, string $token): Response { return $this->ok(true); }
}
