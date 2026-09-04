<?php

namespace App\Listeners;

use App\Services\PaymentConfirmationService;
use Illuminate\Support\Facades\Log;
use SePay\SePay\Events\SePayWebhookEvent;

class SePayWebhookListener
{
    public function __construct(
        private readonly PaymentConfirmationService $paymentConfirmation,
    ) {}

    public function handle(SePayWebhookEvent $event): void
    {
        $data = $event->sePayWebhookData;

        if ($data->transferType !== 'in') {
            Log::info('SePay webhook: ignoring outgoing transfer', [
                'provider_transaction_id' => $data->id,
                'transfer_type' => $data->transferType,
            ]);

            return;
        }

        $orderNumber = config('sepay.pattern').$event->info;

        $this->paymentConfirmation->confirmFromSePay($orderNumber, $data);
    }
}
