<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')
            ->withAvg(['visibleReviews as average_rating' => fn ($q) => $q], 'rating')
            ->where('is_active', true);

        if ($search = $request->string('search')->toString()) {
            $term = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(short_description) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(description) LIKE ?', [$term]);
            });
        }

        if ($category = $request->string('category')->toString()) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category));
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $sort = $request->string('sort')->toString() ?: 'newest';
        match ($sort) {
            'price_asc' => $query->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('created_at'),
        };

        $perPage = min((int) $request->input('per_page', 12), 48);

        return response()->json($query->paginate($perPage));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'visibleReviews.user:id,name'])
            ->withAvg(['visibleReviews as average_rating' => fn ($q) => $q], 'rating')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json($product);
    }

    public function byId(int $id): JsonResponse
    {
        $product = Product::with('category')
            ->where('is_active', true)
            ->findOrFail($id);

        return response()->json($product);
    }
}
