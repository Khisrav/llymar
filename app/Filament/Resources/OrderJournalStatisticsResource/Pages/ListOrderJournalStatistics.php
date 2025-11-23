<?php

namespace App\Filament\Resources\OrderJournalStatisticsResource\Pages;

use App\Filament\Resources\OrderJournalStatisticsResource;
use App\Filament\Resources\OrderJournalStatisticsResource\Widgets\OrderJournalAverageHoursWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrderJournalStatistics extends ListRecords
{
    protected static string $resource = OrderJournalStatisticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Назад к цеху')
                ->url(fn () => route('filament.admin.resources.order-journals.index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OrderJournalAverageHoursWidget::class,
        ];
    }
}
