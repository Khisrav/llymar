<?php

namespace App\Filament\Resources\OrderJournalResource\Pages;

use App\Filament\Resources\OrderJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderJournals extends ListRecords
{
    protected static string $resource = OrderJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
