<?php

namespace App\Filament\Resources\ComissionCreditsResource\Pages;

use App\Filament\Resources\ComissionCreditsResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewComissionCredits extends ViewRecord
{
    protected static string $resource = ComissionCreditsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
} 