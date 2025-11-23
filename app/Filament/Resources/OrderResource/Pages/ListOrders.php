<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Bostos\ReorderableColumns\Concerns\HasReorderableColumns;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrders extends ListRecords
{
    use HasReorderableColumns;
    
    protected static string $resource = OrderResource::class;
    
    protected static ?string $title = 'Заказы';
    
    protected static string $view = 'filament.resources.order-resource.pages.list-orders';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }
}
