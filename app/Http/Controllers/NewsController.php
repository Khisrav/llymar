<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NewsController extends Controller
{
    /**
     * Display a listing of published news.
     */
    public function index(Request $request): Response
    {
        $query = News::with('author')
            ->published()
            ->orderBy('published_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('content', 'like', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'like', "%{$searchTerm}%");
            });
        }

        $news = $query->paginate(12);

        return Inertia::render('News/Index', [
            'news' => $news,
            'search' => $request->get('search', ''),
            'seo' => [
                'title' => 'Новости - Ваша компания',
                'description' => 'Читайте последние новости и статьи о наших продуктах и услугах.',
            ],
        ]);
    }

    /**
     * Display the specified news article.
     */
    public function show(News $news): Response
    {
        // Check if news is published
        if (!$news->isPublished()) {
            abort(404);
        }

        // Increment views
        $news->incrementViews();

        // Load author relationship
        $news->load('author');

        // Get related news (same author or recent)
        $relatedNews = News::published()
            ->where('id', '!=', $news->id)
            ->where(function ($query) use ($news) {
                $query->where('author_id', $news->author_id)
                      ->orWhere('created_at', '>=', now()->subMonths(3));
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return Inertia::render('News/Show', [
            'news' => $news,
            'relatedNews' => $relatedNews,
            'seo' => [
                'title' => $news->seo_title ?: $news->title,
                'description' => $news->seo_description ?: $news->excerpt,
                'image' => $news->cover_image_url,
                'url' => route('news.show', $news),
            ],
        ]);
    }

    /**
     * Get news for API (optional, for future use)
     */
    public function api(Request $request)
    {
        $query = News::published()
            ->select(['id', 'title', 'slug', 'excerpt', 'cover_image', 'published_at', 'views'])
            ->orderBy('published_at', 'desc');

        if ($request->filled('limit')) {
            $query->limit($request->get('limit', 10));
        }

        $news = $query->get();

        return response()->json([
            'data' => $news->map(function ($item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'slug' => $item->slug,
                    'excerpt' => $item->excerpt,
                    'cover_image_url' => $item->cover_image_url,
                    'published_at' => $item->published_at?->format('Y-m-d H:i:s'),
                    'views' => $item->views,
                    'url' => route('news.show', $item),
                ];
            }),
        ]);
    }
}
