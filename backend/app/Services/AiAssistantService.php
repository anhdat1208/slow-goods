<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AiAssistantService
{
    public function ask(string $question, string $locale = 'en'): array
    {
        $catalog = Product::with('category')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->get(['id', 'name', 'slug', 'short_description', 'price', 'category_id', 'image_url', 'is_featured']);

        $apiKey = config('services.openai.api_key');

        if ($apiKey) {
            try {
                return $this->askOpenAi($question, $catalog, $apiKey, $locale);
            } catch (\Throwable $e) {
                // Fall through to mock
            }
        }

        return $this->mockAnswer($question, $catalog, $locale);
    }

    private function askOpenAi(string $question, Collection $catalog, string $apiKey, string $locale = 'en'): array
    {
        $catalogText = $catalog->map(function (Product $product) {
            return sprintf(
                'ID:%d | %s | %s₫ | Category:%s | %s',
                $product->id,
                $product->name,
                number_format((float) $product->price, 0, ',', '.'),
                $product->category?->name,
                $product->short_description
            );
        })->implode("\n");

        $response = Http::withToken($apiKey)
            ->timeout(30)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => config('services.openai.model', 'gpt-4o-mini'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $locale === 'vi'
                            ? 'Bạn là Ask Slow, trợ lý mua sắm điềm tĩnh của Slow Goods (khẩu hiệu: Ít màn hình. Nhiều đời sống.). Chỉ gợi ý sản phẩm trong catalog. Không bịa sản phẩm. Trả lời bằng tiếng Việt, ấm áp, ngắn gọn. Nhắc tên và ID sản phẩm khi gợi ý.'
                            : 'You are Ask Slow, a calm shopping assistant for Slow Goods (tagline: Less screen. More life.). Recommend only products from the provided catalog. Never invent products. Reply in a warm, concise tone. Include product names and IDs when recommending.',
                    ],
                    [
                        'role' => 'user',
                        'content' => "Catalog:\n{$catalogText}\n\nCustomer question: {$question}",
                    ],
                ],
                'temperature' => 0.4,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('OpenAI request failed');
        }

        $answer = data_get($response->json(), 'choices.0.message.content', '');
        $products = $this->extractProductsFromAnswer($answer, $catalog);

        return [
            'mode' => 'openai',
            'answer' => $answer,
            'products' => $products,
        ];
    }

    private function mockAnswer(string $question, Collection $catalog, string $locale = 'en'): array
    {
        $q = Str::lower($question);
        $products = collect();

        if (Str::contains($q, ['read', 'book', 'journal', 'corner', 'đọc', 'sách', 'góc đọc'])) {
            $products = $catalog->filter(fn (Product $p) => in_array($p->category?->slug, ['books', 'writing', 'desk'], true));
        } elseif (Str::contains($q, ['write', 'pen', 'notebook', 'viết', 'bút', 'sổ'])) {
            $products = $catalog->filter(fn (Product $p) => in_array($p->category?->slug, ['writing', 'desk'], true));
        } elseif (Str::contains($q, ['outdoor', 'camp', 'nature', 'outside', 'ngoài trời', 'cắm trại'])) {
            $products = $catalog->filter(fn (Product $p) => in_array($p->category?->slug, ['outdoor', 'writing'], true));
        } elseif (Str::contains($q, ['craft', 'diy', 'make', 'handmade', 'thủ công', 'làm'])) {
            $products = $catalog->filter(fn (Product $p) => $p->category?->slug === 'craft-diy');
        } elseif (Str::contains($q, ['phone', 'screen', 'digital', 'slow', 'điện thoại', 'màn hình'])) {
            $products = $catalog->filter(fn (Product $p) => in_array($p->category?->slug, ['slow-living', 'desk', 'books'], true));
        } elseif (preg_match('/(?:under|dưới)\s*\$?\s*([\d\.\,]+)/u', $q, $m)) {
            $max = (float) str_replace(['.', ','], '', $m[1]);
            if ($max < 1000) {
                $max *= 25000;
            }
            $products = $catalog->filter(fn (Product $p) => (float) $p->price <= $max);
        } else {
            $products = $catalog->where('is_featured', true);
            if ($products->isEmpty()) {
                $products = $catalog->take(6);
            }
        }

        $products = $products->take(4)->values();

        if ($products->isEmpty()) {
            $products = $catalog->take(3)->values();
        }

        $lines = $products->map(function (Product $p) {
            $price = number_format((float) $p->price, 0, ',', '.').'₫';

            return "- {$p->name} ({$price}) — {$p->short_description}";
        })->implode("\n");

        $answer = $locale === 'vi'
            ? "Đây là vài gợi ý điềm tĩnh từ catalog, dựa trên điều bạn hỏi:\n\n{$lines}\n\nÍt màn hình. Nhiều đời sống."
            : "Here's a calm shortlist from our catalog based on what you asked:\n\n{$lines}\n\nLess screen. More life.";

        return [
            'mode' => 'fallback',
            'answer' => $answer,
            'products' => $products->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'image_url' => $p->image_url,
                'short_description' => $p->short_description,
            ])->values()->all(),
        ];
    }

    private function extractProductsFromAnswer(string $answer, Collection $catalog): array
    {
        return $catalog
            ->filter(function (Product $product) use ($answer) {
                return Str::contains($answer, (string) $product->id)
                    || Str::contains($answer, $product->name);
            })
            ->take(6)
            ->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => $p->price,
                'image_url' => $p->image_url,
                'short_description' => $p->short_description,
            ])
            ->values()
            ->all();
    }
}
