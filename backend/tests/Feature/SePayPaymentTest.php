<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SePayPaymentTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookToken = 'test-sepay-webhook-token';

    private function product(array $overrides = []): Product
    {
        $category = Category::create([
            'name' => 'Desk',
            'slug' => 'desk-'.uniqid(),
            'description' => 'Desk',
            'image_url' => 'https://example.com/c.jpg',
        ]);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => '[TEST] Bank Transfer Payment',
            'slug' => 'test-pay-'.uniqid(),
            'description' => 'Payment test product',
            'short_description' => 'Payment test',
            'price' => 20000,
            'stock' => 100,
            'sku' => 'TEST-'.uniqid(),
            'image_url' => 'https://example.com/p.jpg',
            'is_featured' => false,
            'is_active' => true,
        ], $overrides));
    }

    private function createPendingBankOrder(User $user, float $total = 20000): Order
    {
        $product = $this->product(['price' => $total]);

        $this->actingAs($user, 'sanctum')
            ->postJson('/api/cart/items', ['product_id' => $product->id, 'quantity' => 1])
            ->assertCreated();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/orders', [
                'full_name' => 'Ada Lovelace',
                'email' => 'ada@example.com',
                'phone' => '0900000000',
                'address' => '1 Quiet Street',
                'city' => 'Calmville',
                'postal_code' => '10000',
                'payment_method' => 'bank_transfer',
            ])
            ->assertCreated();

        return Order::findOrFail($response->json('id'));
    }

    private function webhookPayload(array $overrides = []): array
    {
        return array_merge([
            'id' => 123456,
            'gateway' => 'MBBank',
            'transactionDate' => '2026-09-04 10:30:00',
            'accountNumber' => '0945455495',
            'subAccount' => null,
            'code' => null,
            'content' => 'Thanh toan SG-TESTREF',
            'transferType' => 'in',
            'description' => 'Thanh toan SG-TESTREF',
            'transferAmount' => 20000,
            'referenceCode' => 'FT123456789',
            'accumulated' => 0,
        ], $overrides);
    }

    private function postWebhook(array $payload, ?string $token = null)
    {
        $token ??= $this->webhookToken;

        return $this->postJson('/api/sepay/webhook', $payload, [
            'Authorization' => 'Apikey '.$token,
        ]);
    }

    public function test_successful_bank_transfer_marks_order_paid(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->postWebhook($this->webhookPayload([
            'id' => 1001,
            'content' => 'Chuyen khoan '.$order->order_number,
            'description' => 'Chuyen khoan '.$order->order_number,
            'transferAmount' => 20000,
        ]))->assertNoContent();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertNotNull($order->paid_at);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider' => 'sepay',
            'provider_transaction_id' => '1001',
            'status' => PaymentTransaction::STATUS_APPLIED,
        ]);
    }

    public function test_wrong_amount_leaves_order_pending(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->postWebhook($this->webhookPayload([
            'id' => 1002,
            'content' => $order->order_number,
            'transferAmount' => 10000,
        ]))->assertNoContent();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertNull($order->paid_at);
        $this->assertDatabaseHas('payment_transactions', [
            'order_id' => $order->id,
            'provider_transaction_id' => '1002',
            'status' => PaymentTransaction::STATUS_IGNORED,
        ]);
    }

    public function test_unknown_reference_does_not_pay_any_order(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->postWebhook($this->webhookPayload([
            'id' => 1003,
            'content' => 'Thanh toan SG-DOESNOTEXIST',
            'transferAmount' => 20000,
        ]))->assertNoContent();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertDatabaseHas('payment_transactions', [
            'payment_reference' => 'SG-DOESNOTEXIST',
            'provider_transaction_id' => '1003',
            'status' => PaymentTransaction::STATUS_IGNORED,
            'order_id' => null,
        ]);
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(0, Order::query()->where('payment_status', Order::PAYMENT_PAID)->count());
    }

    public function test_outgoing_transfer_does_not_mark_order_paid(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->postWebhook($this->webhookPayload([
            'id' => 1004,
            'content' => $order->order_number,
            'transferType' => 'out',
            'transferAmount' => 20000,
        ]))->assertNoContent();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertDatabaseMissing('payment_transactions', [
            'provider_transaction_id' => '1004',
            'status' => PaymentTransaction::STATUS_APPLIED,
        ]);
    }

    public function test_duplicate_transaction_is_idempotent(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $payload = $this->webhookPayload([
            'id' => 1005,
            'content' => $order->order_number,
            'transferAmount' => 20000,
        ]);

        $this->postWebhook($payload)->assertNoContent();
        $this->postWebhook($payload)->assertStatus(422);

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(1, PaymentTransaction::query()->where('provider_transaction_id', '1005')->count());
        $this->assertSame(1, PaymentTransaction::query()->where('status', PaymentTransaction::STATUS_APPLIED)->count());
    }

    public function test_already_paid_order_is_not_processed_again(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->postWebhook($this->webhookPayload([
            'id' => 1006,
            'content' => $order->order_number,
            'transferAmount' => 20000,
        ]))->assertNoContent();

        $this->postWebhook($this->webhookPayload([
            'id' => 1007,
            'content' => $order->order_number,
            'transferAmount' => 20000,
        ]))->assertNoContent();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame(1, PaymentTransaction::query()->where('status', PaymentTransaction::STATUS_APPLIED)->count());
        $this->assertDatabaseHas('payment_transactions', [
            'provider_transaction_id' => '1007',
            'status' => PaymentTransaction::STATUS_IGNORED,
        ]);
    }

    public function test_invalid_webhook_authentication_is_rejected(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->postWebhook($this->webhookPayload([
            'id' => 1008,
            'content' => $order->order_number,
        ]), 'wrong-token')->assertStatus(422);

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertDatabaseCount('payment_transactions', 0);
        $this->assertDatabaseCount('sepay_transactions', 0);
    }

    public function test_webhook_without_accept_json_does_not_redirect(): void
    {
        $payload = $this->webhookPayload([
            'id' => 1009,
            'content' => 'SG-NOACCEPT',
        ]);

        $this->call(
            'POST',
            '/api/sepay/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Apikey wrong-token',
            ],
            json_encode($payload),
        )->assertStatus(422)->assertJson([
            'message' => 'Invalid Token',
        ]);
    }

    public function test_bank_stripping_hyphen_from_transfer_content_still_matches(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);
        $spaced = str_replace('SG-', 'SG ', $order->order_number);

        $this->postWebhook($this->webhookPayload([
            'id' => 1010,
            'content' => 'MBVCB.1.2.'.$spaced.'.CT tu 0921 VO ANH DAT',
            'description' => 'BankAPINotify '.$spaced,
            'transferAmount' => 20000,
        ]))->assertNoContent();

        $order->refresh();

        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
    }

    public function test_hmac_signature_is_accepted(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $payload = $this->webhookPayload([
            'id' => 1011,
            'content' => 'Thanh toan '.$order->order_number,
            'description' => 'Thanh toan '.$order->order_number,
            'transferAmount' => 20000,
        ]);

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$body, $this->webhookToken);

        $this->call(
            'POST',
            '/api/sepay/webhook',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_SEPAY_SIGNATURE' => $signature,
                'HTTP_X_SEPAY_TIMESTAMP' => $timestamp,
            ],
            $body,
        )->assertNoContent();

        $order->refresh();
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
    }

    public function test_payment_status_endpoint_requires_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $order = $this->createPendingBankOrder($owner);

        $this->actingAs($other, 'sanctum')
            ->getJson('/api/orders/'.$order->id.'/payment-status')
            ->assertNotFound();

        $this->actingAs($owner, 'sanctum')
            ->getJson('/api/orders/'.$order->id.'/payment-status')
            ->assertOk()
            ->assertJsonPath('payment_status', Order::PAYMENT_PENDING)
            ->assertJsonPath('order_number', $order->order_number)
            ->assertJsonPath('amount', '20000.00')
            ->assertJsonPath('payment.transfer_content', $order->order_number)
            ->assertJsonStructure([
                'payment' => [
                    'bank_name',
                    'account_number',
                    'account_holder',
                    'qr_image_url',
                    'transfer_content',
                ],
            ]);
    }

    public function test_checkout_with_bank_transfer_sets_pending_payment(): void
    {
        $user = User::factory()->create();
        $order = $this->createPendingBankOrder($user);

        $this->assertSame('bank_transfer', $order->payment_method);
        $this->assertSame(Order::PAYMENT_PENDING, $order->payment_status);
        $this->assertNull($order->paid_at);
        $this->assertStringStartsWith('SG-', $order->order_number);
    }
}
