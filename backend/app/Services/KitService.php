<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class KitService
{
    public function all(): array
    {
        return [
            $this->buildKit('reading-kit', 'Reading Kit', 'A quiet corner starter for deeper reading.', [
                'philosophy-of-enough',
                'reading-journal',
                'linen-bookmark-set',
                'wooden-book-stand',
            ]),
            $this->buildKit('slow-morning-kit', 'Slow Morning Kit', 'Begin the day without a screen.', [
                'stoneware-mug',
                'field-notes-notebook',
                'fountain-pen',
                'analog-alarm-clock',
            ]),
            $this->buildKit('outdoor-kit', 'Outdoor Kit', 'Carry less. Notice more.', [
                'field-notes-notebook',
                'camping-mug',
                'mechanical-pencil',
                'pocket-compass',
            ]),
        ];
    }

    public function find(string $slug): ?array
    {
        return collect($this->all())->firstWhere('slug', $slug);
    }

    private function buildKit(string $slug, string $name, string $description, array $productSlugs): array
    {
        /** @var Collection<int, Product> $products */
        $products = Product::with('category')
            ->whereIn('slug', $productSlugs)
            ->where('is_active', true)
            ->get();

        $ordered = collect($productSlugs)
            ->map(fn ($s) => $products->firstWhere('slug', $s))
            ->filter()
            ->values();

        $total = $ordered->reduce(fn ($carry, Product $p) => bcadd((string) $carry, (string) $p->price, 2), '0.00');

        return [
            'slug' => $slug,
            'name' => $name,
            'description' => $description,
            'total_price' => $total,
            'products' => $ordered,
        ];
    }
}
