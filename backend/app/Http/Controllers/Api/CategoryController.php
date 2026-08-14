<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function show(string $slug): JsonResponse
    {
        $category = Category::with(['products' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json($category);
    }
}
