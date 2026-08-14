<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiAssistantService;
use App\Services\KitService;
use App\Services\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function goals(RecommendationService $recommendations): JsonResponse
    {
        return response()->json($recommendations->goals());
    }

    public function goalProducts(string $goal, RecommendationService $recommendations): JsonResponse
    {
        return response()->json($recommendations->forGoal($goal));
    }

    public function kits(KitService $kits): JsonResponse
    {
        return response()->json($kits->all());
    }

    public function kit(string $slug, KitService $kits): JsonResponse
    {
        $kit = $kits->find($slug);

        abort_if(! $kit, 404);

        return response()->json($kit);
    }

    public function ask(Request $request, AiAssistantService $ai): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:1000'],
            'locale' => ['nullable', 'in:en,vi'],
        ]);

        return response()->json($ai->ask($data['question'], $data['locale'] ?? 'en'));
    }
}
