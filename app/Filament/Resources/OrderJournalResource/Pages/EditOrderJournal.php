<?php

namespace App\Filament\Resources\OrderJournalResource\Pages;

use App\Filament\Resources\OrderJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderJournal extends EditRecord
{
    protected static string $resource = OrderJournalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
