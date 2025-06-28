<?php

namespace App\Providers;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Проверка на доверенные прокси (если используешь nginx, beget, cloudflare и т.п.)
        if (request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }
    
        // Устанавливаем APP_URL динамически
        $host = request()->getSchemeAndHttpHost();
        URL::forceRootUrl($host);
        
        Vite::prefetch(concurrency: 3);
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('yandex', \SocialiteProviders\Yandex\Provider::class);
        });
        
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super-Admin') ? true : null;
        });
        
        FilamentColor::register([
            'red' => Color::Red,
            'blue' => Color::Blue,
            'green' => Color::Green,
            'yellow' => Color::Yellow,
            'purple' => Color::Purple,
            'orange' => Color::Orange,
            'pink' => Color::Pink,
            'gray' => Color::Gray,
            'cyan' => Color::Cyan,
            'emerald' => Color::Emerald,
            'teal' => Color::Teal,
            'indigo' => Color::Indigo,
            'violet' => Color::Violet,
            'lime' => Color::Lime,
            'fuchsia' => Color::Fuchsia,
        ]);
    }
}
