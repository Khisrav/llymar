<?php

namespace App\Filament\Resources\CommercialOfferResource\Pages;

use App\Filament\Resources\CommercialOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCommercialOffer extends CreateRecord
{
    protected static string $resource = CommercialOfferResource::class;

    protected static ?string $title = 'Создание коммерческого предложения';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
