<?php

namespace App\Filament\Resources\WarehouseRecordResource\Pages;

use App\Filament\Resources\WarehouseRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWarehouseRecords extends ListRecords
{
    protected static string $resource = WarehouseRecordResource::class;
    
    protected static ?string $title = 'Записи склада';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
