<?php

namespace App\Filament\Resources\CommercialOfferResource\Pages;

use App\Filament\Resources\CommercialOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommercialOffers extends ListRecords
{
    protected static string $resource = CommercialOfferResource::class;

    protected static ?string $title = 'Коммерческие предложения';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('Создать коммерческое предложение'),
        ];
    }
}
