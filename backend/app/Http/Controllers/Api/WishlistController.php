<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = Wishlist::with('product.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json($items);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        Product::where('is_active', true)->findOrFail($data['product_id']);

        $item = Wishlist::firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $data['product_id'],
        ]);

        return response()->json($item->load('product'), $item->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, int $productId): JsonResponse
    {
        Wishlist::where('user_id', $request->user()->id)
            ->where('product_id', $productId)
            ->delete();

        return response()->json(['message' => 'Removed from wishlist']);
    }
}
