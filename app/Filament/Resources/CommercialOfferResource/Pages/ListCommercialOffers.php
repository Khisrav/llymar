<?php

namespace App\Filament\Resources\CommercialOfferResource\Pages;

use App\Filament\Resources\CommercialOfferResource;
use Bostos\ReorderableColumns\Concerns\HasReorderableColumns;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCommercialOffers extends ListRecords
{
    use HasReorderableColumns;
    
    protected static string $resource = CommercialOfferResource::class;

    protected static ?string $title = 'Коммерческие предложения';
    
    protected static string $view = 'filament.resources.commercial-offer-resource.pages.list-commercial-offers';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make()
            //     ->label('Создать коммерческое предложение'),
        ];
    }
}
