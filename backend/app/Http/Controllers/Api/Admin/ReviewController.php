<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['user:id,name,email', 'product:id,name,slug'])->latest();

        if ($request->has('hidden')) {
            $query->where('is_hidden', $request->boolean('hidden'));
        }

        return response()->json($query->paginate(20));
    }

    public function hide(int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update(['is_hidden' => true]);

        return response()->json($review);
    }

    public function destroy(int $id): JsonResponse
    {
        Review::findOrFail($id)->delete();

        return response()->json(['message' => 'Review deleted']);
    }
}
