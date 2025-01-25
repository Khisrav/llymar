<?php

namespace App\Filament\Resources\WarehouseRecordResource\Pages;

use App\Filament\Resources\WarehouseRecordResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWarehouseRecord extends CreateRecord
{
    protected static string $resource = WarehouseRecordResource::class;
    
    protected static ?string $title = 'Добавить запись в склад';
}
