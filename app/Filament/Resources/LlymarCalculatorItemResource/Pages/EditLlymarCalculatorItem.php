<?php

namespace App\Filament\Resources\LlymarCalculatorItemResource\Pages;

use App\Filament\Resources\LlymarCalculatorItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLlymarCalculatorItem extends EditRecord
{
    protected static string $resource = LlymarCalculatorItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
