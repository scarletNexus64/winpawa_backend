<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalPage;
use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Get a legal page by type
     *
     * @param string $type (privacy, terms, cookies, data_protection)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($type)
    {
        // Validate type
        $validTypes = ['privacy', 'terms', 'cookies', 'data_protection'];

        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Type de page invalide.',
            ], 400);
        }

        // Get active legal page
        $legalPage = LegalPage::where('type', $type)
            ->where('is_active', true)
            ->first();

        if (!$legalPage) {
            return response()->json([
                'success' => false,
                'message' => 'Page non trouvée.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $legalPage->type,
                'title' => $legalPage->title,
                'content' => $legalPage->content,
                'last_updated_at' => $legalPage->last_updated_at,
            ],
        ]);
    }

    /**
     * Get all active legal pages
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $legalPages = LegalPage::where('is_active', true)
            ->select('type', 'title', 'last_updated_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $legalPages,
        ]);
    }
}
