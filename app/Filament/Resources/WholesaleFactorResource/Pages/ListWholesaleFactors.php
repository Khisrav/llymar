<?php

namespace App\Filament\Resources\WholesaleFactorResource\Pages;

use App\Filament\Resources\WholesaleFactorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWholesaleFactors extends ListRecords
{
    protected static string $resource = WholesaleFactorResource::class;
    
    protected static ?string $title = 'Группы коэффициентов';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
