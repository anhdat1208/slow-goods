<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = CartItem::with('product.category')
            ->where('user_id', $request->user()->id)
            ->get();

        $subtotal = $items->reduce(function ($carry, CartItem $item) {
            if (! $item->product) {
                return $carry;
            }

            return bcadd((string) $carry, bcmul((string) $item->product->price, (string) $item->quantity, 2), 2);
        }, '0.00');

        return response()->json([
            'items' => $items,
            'subtotal' => $subtotal,
            'total_quantity' => $items->sum('quantity'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::where('is_active', true)->findOrFail($data['product_id']);

        $item = CartItem::firstOrNew([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        $newQty = ($item->exists ? $item->quantity : 0) + $data['quantity'];

        if ($newQty > $product->stock) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$product->stock} in stock."],
            ]);
        }

        $item->quantity = $newQty;
        $item->save();

        return response()->json($item->load('product'), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $item = CartItem::with('product')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        if ($data['quantity'] > $item->product->stock) {
            throw ValidationException::withMessages([
                'quantity' => ["Only {$item->product->stock} in stock."],
            ]);
        }

        $item->update(['quantity' => $data['quantity']]);

        return response()->json($item->load('product'));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = CartItem::where('user_id', $request->user()->id)->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Removed']);
    }

    public function clear(Request $request): JsonResponse
    {
        CartItem::where('user_id', $request->user()->id)->delete();

        return response()->json(['message' => 'Cart cleared']);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        foreach ($data['items'] as $row) {
            $product = Product::where('is_active', true)->find($row['product_id']);
            if (! $product) {
                continue;
            }

            $item = CartItem::firstOrNew([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ]);

            $qty = min($row['quantity'], $product->stock);
            if ($qty < 1) {
                if ($item->exists) {
                    $item->delete();
                }
                continue;
            }

            $item->quantity = $qty;
            $item->save();
        }

        return $this->index($request);
    }
}
