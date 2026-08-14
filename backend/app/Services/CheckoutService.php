<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function checkout(User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $cartItems = CartItem::with('product')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Your cart is empty.'],
                ]);
            }

            $subtotal = 0;
            $orderItems = [];

            foreach ($cartItems as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages([
                        'cart' => ["Product \"{$item->product?->name}\" is unavailable."],
                    ]);
                }

                if ($product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'cart' => ["Insufficient stock for \"{$product->name}\". Available: {$product->stock}."],
                    ]);
                }

                $lineTotal = bcmul((string) $product->price, (string) $item->quantity, 2);
                $subtotal = bcadd((string) $subtotal, $lineTotal, 2);

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $item->quantity,
                    'line_total' => $lineTotal,
                ];

                $product->decrement('stock', $item->quantity);
            }

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'SG-'.strtoupper(uniqid()),
                'status' => 'pending',
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'],
                'payment_method' => $data['payment_method'],
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            foreach ($orderItems as $orderItem) {
                $order->items()->create($orderItem);
            }

            CartItem::where('user_id', $user->id)->delete();

            return $order->load('items');
        });
    }
}
