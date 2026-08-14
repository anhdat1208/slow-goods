<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class RecommendationService
{
    private const GOAL_MAP = [
        'read-more' => ['books', 'writing', 'desk'],
        'write-more' => ['writing', 'desk', 'books'],
        'slow-down' => ['slow-living', 'books', 'desk'],
        'spend-time-outdoors' => ['outdoor', 'writing'],
        'make-things' => ['craft-diy'],
        'create-a-morning-ritual' => ['slow-living', 'writing', 'desk'],
        'reduce-screen-time' => ['books', 'slow-living', 'outdoor', 'writing'],
    ];

    public function forGoal(string $goal): Collection
    {
        $slugs = self::GOAL_MAP[$goal] ?? [];

        if (empty($slugs)) {
            return collect();
        }

        return Product::with('category')
            ->where('is_active', true)
            ->whereHas('category', fn ($q) => $q->whereIn('slug', $slugs))
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->limit(12)
            ->get();
    }

    public function goals(): array
    {
        return [
            ['slug' => 'read-more', 'label' => 'Read more', 'categories' => self::GOAL_MAP['read-more']],
            ['slug' => 'write-more', 'label' => 'Write more', 'categories' => self::GOAL_MAP['write-more']],
            ['slug' => 'slow-down', 'label' => 'Slow down', 'categories' => self::GOAL_MAP['slow-down']],
            ['slug' => 'spend-time-outdoors', 'label' => 'Spend time outdoors', 'categories' => self::GOAL_MAP['spend-time-outdoors']],
            ['slug' => 'make-things', 'label' => 'Make things', 'categories' => self::GOAL_MAP['make-things']],
            ['slug' => 'create-a-morning-ritual', 'label' => 'Create a morning ritual', 'categories' => self::GOAL_MAP['create-a-morning-ritual']],
            ['slug' => 'reduce-screen-time', 'label' => 'Reduce screen time', 'categories' => self::GOAL_MAP['reduce-screen-time']],
        ];
    }
}
