<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SePay\SePay\Datas\SePayWebhookData;
use Throwable;

class PaymentConfirmationService
{
    public const PROVIDER = 'sepay';

    public function confirmFromSePay(string $orderNumber, SePayWebhookData $data): void
    {
        try {
            DB::transaction(function () use ($orderNumber, $data) {
                $existing = PaymentTransaction::query()
                    ->where('provider', self::PROVIDER)
                    ->where('provider_transaction_id', (string) $data->id)
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    Log::info('SePay payment already recorded', [
                        'provider_transaction_id' => $data->id,
                        'payment_transaction_id' => $existing->id,
                    ]);

                    return;
                }

                $order = Order::query()
                    ->where('order_number', $orderNumber)
                    ->lockForUpdate()
                    ->first();

                if (! $order) {
                    $this->recordIgnored($data, $orderNumber, null, 'unknown_payment_reference');

                    Log::warning('SePay webhook: unknown payment reference', [
                        'order_number' => $orderNumber,
                        'provider_transaction_id' => $data->id,
                    ]);

                    return;
                }

                if ($order->payment_method !== 'bank_transfer') {
                    $this->recordIgnored($data, $orderNumber, $order->id, 'not_bank_transfer');

                    Log::warning('SePay webhook: order is not bank transfer', [
                        'order_id' => $order->id,
                        'payment_method' => $order->payment_method,
                    ]);

                    return;
                }

                if ($order->payment_status === Order::PAYMENT_PAID) {
                    $this->recordIgnored($data, $orderNumber, $order->id, 'already_paid');

                    Log::info('SePay webhook: order already paid', [
                        'order_id' => $order->id,
                        'provider_transaction_id' => $data->id,
                    ]);

                    return;
                }

                $expectedAmount = $this->orderAmountAsInteger($order->total);
                $receivedAmount = (int) $data->transferAmount;

                if ($receivedAmount < $expectedAmount) {
                    $this->recordIgnored($data, $orderNumber, $order->id, 'insufficient_amount', [
                        'expected' => $expectedAmount,
                        'received' => $receivedAmount,
                    ]);

                    Log::warning('SePay webhook: insufficient amount', [
                        'order_id' => $order->id,
                        'expected' => $expectedAmount,
                        'received' => $receivedAmount,
                    ]);

                    return;
                }

                PaymentTransaction::create([
                    'order_id' => $order->id,
                    'provider' => self::PROVIDER,
                    'provider_transaction_id' => (string) $data->id,
                    'payment_reference' => $orderNumber,
                    'amount' => $receivedAmount,
                    'transaction_date' => $data->transactionDate ?: null,
                    'transfer_type' => $data->transferType,
                    'description' => $data->content ?: $data->description,
                    'status' => PaymentTransaction::STATUS_APPLIED,
                    'raw_payload' => $this->payloadArray($data),
                ]);

                $order->update([
                    'payment_status' => Order::PAYMENT_PAID,
                    'paid_at' => now(),
                ]);
            });
        } catch (Throwable $e) {
            Log::error('SePay payment confirmation failed', [
                'order_number' => $orderNumber,
                'provider_transaction_id' => $data->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function recordIgnored(
        SePayWebhookData $data,
        string $orderNumber,
        ?int $orderId,
        string $reason,
        array $extra = [],
    ): void {
        PaymentTransaction::create([
            'order_id' => $orderId,
            'provider' => self::PROVIDER,
            'provider_transaction_id' => (string) $data->id,
            'payment_reference' => $orderNumber,
            'amount' => $data->transferAmount,
            'transaction_date' => $data->transactionDate ?: null,
            'transfer_type' => $data->transferType,
            'description' => $data->content ?: $data->description,
            'status' => PaymentTransaction::STATUS_IGNORED,
            'raw_payload' => array_merge($this->payloadArray($data), [
                'ignore_reason' => $reason,
                ...$extra,
            ]),
        ]);
    }

    private function orderAmountAsInteger(mixed $total): int
    {
        return (int) round((float) $total);
    }

    private function payloadArray(SePayWebhookData $data): array
    {
        return [
            'id' => $data->id,
            'gateway' => $data->gateway,
            'transactionDate' => $data->transactionDate,
            'accountNumber' => $data->accountNumber,
            'subAccount' => $data->subAccount,
            'code' => $data->code,
            'content' => $data->content,
            'transferType' => $data->transferType,
            'description' => $data->description,
            'transferAmount' => $data->transferAmount,
            'referenceCode' => $data->referenceCode,
            'accumulated' => $data->accumulated,
        ];
    }
}
