<?php

namespace App\Http\Controllers;

use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PortfolioController extends Controller
{
    /**
     * Get the latest 4 portfolio records for API
     * 
     * This endpoint returns the 4 most recent portfolio entries.
     * It's protected by CORS restrictions and rate limiting.
     * 
     * @return JsonResponse
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "id": 1,
     *       "title": "Project Title",
     *       "description": "Project description",
     *       "images": ["path/to/image1.jpg", "path/to/image2.jpg"],
     *       "area": 56,
     *       "color": "Black",
     *       "glass": "Bronze 10mm",
     *       "location": "City",
     *       "year": 2025,
     *       "created_at": "2025-08-08T06:31:20.000000Z"
     *     }
     *   ],
     *   "count": 1
     * }
     */
    public function getLatest(): JsonResponse
    {
        $portfolios = Portfolio::latest()
            ->take(4)
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
}
