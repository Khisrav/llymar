<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Resources\ItemResource;
use Bostos\ReorderableColumns\Concerns\HasReorderableColumns;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItems extends ListRecords
{
    use HasReorderableColumns;
    
    protected static string $resource = ItemResource::class;
    
    protected static ?string $title = 'Товары';
    
    protected static string $view = 'filament.resources.item-resource.pages.list-items';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
