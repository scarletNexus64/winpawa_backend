<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GameCategory;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = GameCategory::active()
            ->ordered()
            ->withCount('games')
            ->get()
            ->map(fn ($category) => $this->formatCategory($category));

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function show(GameCategory $category): JsonResponse
    {
        if (!$category->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cette catégorie n\'est pas disponible.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->formatCategory($category, true),
        ]);
    }

    protected function formatCategory(GameCategory $category, bool $withDetails = false): array
    {
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'icon' => $category->icon,
            'color' => $category->color,
            'description' => $category->description,
            'games_count' => $category->games_count ?? 0,
        ];

        if ($withDetails) {
            $data['games'] = $category->games()
                ->active()
                ->ordered()
                ->get()
                ->map(fn ($game) => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'type' => $game->type,
                    'thumbnail' => $game->image ? url('images/' . $game->image) : null,
                    'banner' => $game->banner ? url('storage/' . $game->banner) : null,
                    'rtp' => (float) $game->rtp,
                    'min_bet' => (float) $game->min_bet,
                    'max_bet' => (float) $game->max_bet,
                    'multipliers' => $game->multipliers,
                    'is_featured' => $game->is_featured,
                ]);
        }

        return $data;
    }
}
