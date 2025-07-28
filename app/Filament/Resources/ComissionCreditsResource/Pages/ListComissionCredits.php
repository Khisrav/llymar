<?php

namespace App\Filament\Resources\ComissionCreditsResource\Pages;

use App\Filament\Resources\ComissionCreditsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListComissionCredits extends ListRecords
{
    protected static string $resource = ComissionCreditsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
