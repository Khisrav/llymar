<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\News;
use App\Models\Portfolio;
use Carbon\Carbon;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();
        
        // Add static pages
        $this->addStaticPages($sitemap);
        
        // Add dynamic content
        $this->addNewsPages($sitemap);
        $this->addPortfolioPages($sitemap);
        
        // Write to file
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        // Return XML response
        return response()->file(public_path('sitemap.xml'), [
            'Content-Type' => 'application/xml'
        ]);
    }
    
    /**
     * Add static pages to sitemap
     */
    private function addStaticPages(Sitemap $sitemap): void
    {
        // Home page
        $sitemap->add(
            Url::create(url('/'))
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );
        
        // About Glazing System
        $sitemap->add(
            Url::create(url('/about-glazing-system'))
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.8)
        );
        
        // Portfolio Index
        $sitemap->add(
            Url::create(route('portfolio.index'))
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.9)
        );
        
        // News/Articles Index
        $sitemap->add(
            Url::create(route('news.index'))
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(0.9)
        );
        
        // Auth page
        $sitemap->add(
            Url::create(route('auth'))
                ->setLastModificationDate(Carbon::now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                ->setPriority(0.5)
        );
    }
    
    /**
     * Add news/articles pages to sitemap
     */
    private function addNewsPages(Sitemap $sitemap): void
    {
        News::published()
            ->orderBy('published_at', 'desc')
            ->get()
            ->each(function (News $news) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('news.show', $news->slug))
                        ->setLastModificationDate($news->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.7)
                );
            });
    }
    
    /**
     * Add portfolio pages to sitemap
     */
    private function addPortfolioPages(Sitemap $sitemap): void
    {
        Portfolio::orderBy('created_at', 'desc')
            ->get()
            ->each(function (Portfolio $portfolio) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('portfolio.show', $portfolio->id))
                        ->setLastModificationDate($portfolio->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setPriority(0.8)
                );
            });
    }
}
