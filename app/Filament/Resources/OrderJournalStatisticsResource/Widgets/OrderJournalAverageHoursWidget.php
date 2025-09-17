<?php

namespace App\Filament\Resources\OrderJournalStatisticsResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderJournalAverageHoursWidget extends BaseWidget
{
    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // Base query for orders with 'paid', 'completed', 'assembled', 'sent' statuses
        $baseQuery = Order::whereIn('status', ['paid', 'completed', 'assembled', 'sent']);

        // Calculate average hours for each date column
        $avgSketchSentHours = $this->calculateAverageHours($baseQuery->clone(), 'sketched_at');
        $avgCutHours = $this->calculateAverageHours($baseQuery->clone(), 'cut_at');
        $avgPaintedHours = $this->calculateAverageHours($baseQuery->clone(), 'painted_at');
        $avgPackedHours = $this->calculateAverageHours($baseQuery->clone(), 'packed_at');
        $avgSwornHours = $this->calculateAverageHours($baseQuery->clone(), 'sworn_at');
        $avgGlassReworkHours = $this->calculateAverageHours($baseQuery->clone(), 'glass_rework_at');
        $avgGlassComplaintHours = $this->calculateAverageHours($baseQuery->clone(), 'glass_complaint_at');
        $avgGlassReadyHours = $this->calculateAverageHours($baseQuery->clone(), 'glass_ready_at');
        $avgCompletedHours = $this->calculateAverageHours($baseQuery->clone(), 'completed_at');

        return [
            Stat::make('Среднее время чертежа', $avgSketchSentHours !== null ? number_format($avgSketchSentHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время с чертежа')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('primary'),

            Stat::make('Среднее время распила', $avgCutHours !== null ? number_format($avgCutHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время с распила')
                ->descriptionIcon('heroicon-m-scissors')
                ->color('warning'),

            Stat::make('Среднее время покраски', $avgPaintedHours !== null ? number_format($avgPaintedHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время с покраски')
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('success'),

            Stat::make('Среднее время упаковки', $avgPackedHours !== null ? number_format($avgPackedHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время с упаковки')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Среднее время поклейки', $avgSwornHours !== null ? number_format($avgSwornHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время с поклейки')
                ->descriptionIcon('heroicon-m-document-plus')
                ->color('indigo'),

            Stat::make('Переделка стекла', $avgGlassReworkHours !== null ? number_format($avgGlassReworkHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время переделки')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('danger'),

            Stat::make('Рекламация стекла', $avgGlassComplaintHours !== null ? number_format($avgGlassComplaintHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время рекламации')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Готовность стекла', $avgGlassReadyHours !== null ? number_format($avgGlassReadyHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время готовности')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Завершение заказа', $avgCompletedHours !== null ? number_format($avgCompletedHours, 2) . 'ч' : 'Н/Д')
                ->description('Среднее время завершения')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
        ];
    }

    private function calculateAverageHours($query, string $dateColumn): ?float
    {
        $orders = $query->whereNotNull($dateColumn)->get();
        
        if ($orders->isEmpty()) {
            return null;
        }

        $totalHours = 0;
        $count = 0;

        foreach ($orders as $order) {
            if ($order->{$dateColumn}) {
                $date = Carbon::parse($order->{$dateColumn});
                $hoursAgo = $date->diffInHours(now(), true); // Use absolute value for consistent calculations
                $totalHours += $hoursAgo;
                $count++;
            }
        }

        return $count > 0 ? $totalHours / $count : null;
    }
} 