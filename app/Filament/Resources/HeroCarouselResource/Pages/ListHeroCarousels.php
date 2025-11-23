<?php

namespace App\Filament\Resources\HeroCarouselResource\Pages;

use App\Filament\Resources\HeroCarouselResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeroCarousels extends ListRecords
{
    protected static string $resource = HeroCarouselResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
