<?php

namespace App\Filament\Resources\ComissionCreditsResource\Pages;

use App\Filament\Resources\ComissionCreditsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditComissionCredits extends EditRecord
{
    protected static string $resource = ComissionCreditsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
