<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('category')->latest();

        if ($search = $request->string('search')->toString()) {
            $term = '%'.mb_strtolower($search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(sku) LIKE ?', [$term]);
            });
        }

        return response()->json($query->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        $product = Product::create($data);

        return response()->json($product->load('category'), 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $data = $this->validated($request, $product->id);

        if (isset($data['name']) && $data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        $product->update($data);

        return response()->json($product->fresh()->load('category'));
    }

    public function destroy(int $id): JsonResponse
    {
        Product::findOrFail($id)->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    private function validated(Request $request, ?int $productId = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'short_description' => ['required', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'sku' => ['required', 'string', 'max:100', 'unique:products,sku,'.($productId ?? 'NULL')],
            'image_url' => ['nullable', 'string', 'max:500'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            Product::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
