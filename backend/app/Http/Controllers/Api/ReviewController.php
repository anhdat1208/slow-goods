<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::where('is_active', true)->findOrFail($productId);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'is_hidden' => false,
            ]
        );

        return response()->json($review->load('user:id,name'), $review->wasRecentlyCreated ? 201 : 200);
    }
}
