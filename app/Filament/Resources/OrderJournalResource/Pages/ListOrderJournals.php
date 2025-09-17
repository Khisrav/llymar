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
            Actions\Action::make('statistics')
                ->label('Статистика')
                ->url(fn () => route('filament.admin.resources.order-journal-statistics.index'))
                ->color('gray')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
