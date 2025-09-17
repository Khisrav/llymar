<?php

namespace App\Filament\Resources\OrderJournalStatisticsResource\Pages;

use App\Filament\Resources\OrderJournalStatisticsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrderJournalStatistics extends EditRecord
{
    protected static string $resource = OrderJournalStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
