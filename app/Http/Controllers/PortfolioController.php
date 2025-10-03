<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    /**
     * Get the latest 8 portfolio records for carousel
     * 
     * This endpoint returns the 8 most recent portfolio entries for homepage carousel.
     * It's protected by CORS restrictions and rate limiting.
     * 
     * @return JsonResponse
     */
    public function getLatest(): JsonResponse
    {
        $portfolios = Portfolio::latest()
            ->take(8)
            ->get([
                'id',
                'title',
                'description',
                'images',
                'area',
                'color',
                'glass',
                'location',
                'year',
                'created_at'
            ]);

        return response()->json([
            'success' => true,
            'data' => $portfolios,
            'count' => $portfolios->count()
        ]);
    }

    /**
     * Get all portfolios for catalogue page
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $portfolios = Portfolio::latest()
            ->get([
                'id',
                'title',
                'description',
                'images',
                'area',
                'color',
                'glass',
                'location',
                'year',
                'created_at'
            ]);

        return response()->json([
            'success' => true,
            'data' => $portfolios,
            'count' => $portfolios->count()
        ]);
    }

    /**
     * Get single portfolio by ID
     * 
     * @param Portfolio $portfolio
     * @return JsonResponse
     */
    public function show(Portfolio $portfolio): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $portfolio
        ]);
    }
}
