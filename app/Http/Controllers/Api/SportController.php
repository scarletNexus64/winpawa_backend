<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sport;
use App\Models\SportCategory;
use App\Models\SportMatch;
use Illuminate\Http\JsonResponse;

class SportController extends Controller
{
    public function categories(): JsonResponse
    {
        $categories = SportCategory::active()
            ->ordered()
            ->withCount('sports')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'image' => $category->image ? url('images/' . $category->image) : null,
                'color' => $category->color,
                'description' => $category->description,
                'sports_count' => $category->sports_count ?? 0,
            ]);

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function sports(): JsonResponse
    {
        $sports = Sport::with('category:id,name,slug')
            ->withCount(['matches' => function ($query) {
                $query->where('status', 'upcoming');
            }])
            ->ordered()
            ->get()
            ->map(fn ($sport) => [
                'id' => $sport->id,
                'name' => $sport->name,
                'slug' => $sport->slug,
                'type' => $sport->type,
                'icon' => $sport->icon,
                'image' => $sport->image ? url('images/' . $sport->image) : null,
                'description' => $sport->description,
                'is_live' => $sport->is_live,
                'is_virtual' => $sport->is_virtual,
                'is_active' => $sport->is_active,
                'matches_count' => $sport->matches_count ?? 0,
                'category' => $sport->category ? [
                    'id' => $sport->category->id,
                    'name' => $sport->category->name,
                    'slug' => $sport->category->slug,
                ] : null,
            ]);

        return response()->json([
            'success' => true,
            'data' => $sports,
        ]);
    }

    public function matches(string $sportSlug = null): JsonResponse
    {
        $query = SportMatch::with(['sport:id,name,slug,type,icon']);

        if ($sportSlug) {
            $query->whereHas('sport', function ($q) use ($sportSlug) {
                $q->where('slug', $sportSlug);
            });
        }

        $matches = $query->upcoming()
            ->limit(20)
            ->get()
            ->map(fn ($match) => [
                'id' => $match->id,
                'home_team' => $match->home_team,
                'away_team' => $match->away_team,
                'home_logo' => $match->home_logo ? url('images/' . $match->home_logo) : null,
                'away_logo' => $match->away_logo ? url('images/' . $match->away_logo) : null,
                'league' => $match->league,
                'match_time' => $match->match_time->toIso8601String(),
                'status' => $match->status,
                'odds' => $match->odds,
                'sport' => [
                    'id' => $match->sport->id,
                    'name' => $match->sport->name,
                    'type' => $match->sport->type,
                    'icon' => $match->sport->icon,
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }

    public function liveMatches(): JsonResponse
    {
        $matches = SportMatch::with(['sport:id,name,slug,type,icon'])
            ->live()
            ->get()
            ->map(fn ($match) => [
                'id' => $match->id,
                'home_team' => $match->home_team,
                'away_team' => $match->away_team,
                'home_score' => $match->home_score,
                'away_score' => $match->away_score,
                'league' => $match->league,
                'odds' => $match->odds,
                'sport' => [
                    'id' => $match->sport->id,
                    'name' => $match->sport->name,
                    'type' => $match->sport->type,
                ],
            ]);

        return response()->json([
            'success' => true,
            'data' => $matches,
        ]);
    }
}
