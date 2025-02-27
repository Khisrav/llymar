<?php

namespace App\Filament\Resources\LlymarCalculatorItemResource\Pages;

use App\Filament\Resources\LlymarCalculatorItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLlymarCalculatorItems extends ListRecords
{
    protected static string $resource = LlymarCalculatorItemResource::class;
    protected static ?string $title = 'Состав системы';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
