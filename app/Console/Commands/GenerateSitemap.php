<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\News;
use App\Models\Portfolio;
use Carbon\Carbon;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap.xml file for the website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating sitemap...');
        
        $sitemap = Sitemap::create();
        
        // Add static pages
        $this->addStaticPages($sitemap);
        $this->info('✓ Added static pages');
        
        // Add dynamic content
        $newsCount = $this->addNewsPages($sitemap);
        $this->info("✓ Added {$newsCount} news articles");
        
        $portfolioCount = $this->addPortfolioPages($sitemap);
        $this->info("✓ Added {$portfolioCount} portfolio items");
        
        // Write to file
        $sitemap->writeToFile(public_path('sitemap.xml'));
        
        $this->info('✓ Sitemap generated successfully at: ' . public_path('sitemap.xml'));
        
        return Command::SUCCESS;
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
    private function addNewsPages(Sitemap $sitemap): int
    {
        $news = News::published()
            ->orderBy('published_at', 'desc')
            ->get();
            
        $news->each(function (News $newsItem) use ($sitemap) {
            $sitemap->add(
                Url::create(route('news.show', $newsItem->slug))
                    ->setLastModificationDate($newsItem->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7)
            );
        });
        
        return $news->count();
    }
    
    /**
     * Add portfolio pages to sitemap
     */
    private function addPortfolioPages(Sitemap $sitemap): int
    {
        $portfolios = Portfolio::orderBy('created_at', 'desc')
            ->get();
            
        $portfolios->each(function (Portfolio $portfolio) use ($sitemap) {
            $sitemap->add(
                Url::create(route('portfolio.show', $portfolio->id))
                    ->setLastModificationDate($portfolio->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.8)
            );
        });
        
        return $portfolios->count();
    }
}
