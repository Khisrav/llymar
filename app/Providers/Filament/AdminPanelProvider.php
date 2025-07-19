<?php

namespace App\Providers\Filament;

use App\Filament\Resources\OrderResource\Widgets\StatsOverview;
use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Fuchsia,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // Widgets\FilamentInfoWidget::class,
                StatsOverview::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->sidebarCollapsibleOnDesktop()
            ->breadcrumbs(false)
            ->maxContentWidth(MaxWidth::Full)
            ->navigationItems([
                NavigationItem::make('Калькулятор')
                    ->url('/app/calculator')
                    ->icon('heroicon-o-calculator'),
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Договоры')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label('Пользователи')
                    // ->collapsed()
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make()
                    ->label('Склад')
                    // ->collapsed()
                    ->icon('heroicon-o-truck'),
                NavigationGroup::make()
                    ->label('Настройки')
                    // ->collapsed()
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->spa();
    }
}
