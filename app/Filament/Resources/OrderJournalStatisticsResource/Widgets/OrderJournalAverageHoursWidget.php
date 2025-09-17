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
        // Base query for orders with 'paid' status
        $baseQuery = Order::where('status', 'paid');

        // Calculate average hours for each date column
        $avgSketchedHours = $this->calculateAverageHours($baseQuery->clone(), 'sketched_at');
        $avgCutHours = $this->calculateAverageHours($baseQuery->clone(), 'cut_at');
        $avgPaintedHours = $this->calculateAverageHours($baseQuery->clone(), 'painted_at');
        $avgPackedHours = $this->calculateAverageHours($baseQuery->clone(), 'packed_at');
        $avgSwornHours = $this->calculateAverageHours($baseQuery->clone(), 'sworn_at');
        $avgGlassReworkHours = $this->calculateAverageHours($baseQuery->clone(), 'glass_rework_at');
        $avgGlassComplaintHours = $this->calculateAverageHours($baseQuery->clone(), 'glass_complaint_at');
        $avgGlassReadyHours = $this->calculateAverageHours($baseQuery->clone(), 'glass_ready_at');
        $avgCompletedHours = $this->calculateAverageHours($baseQuery->clone(), 'completed_at');

        return [
            Stat::make('Среднее время чертежа', $avgSketchedHours !== null ? round($avgSketchedHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время с чертежа')
                ->descriptionIcon('heroicon-m-pencil')
                ->color('primary'),

            Stat::make('Среднее время распила', $avgCutHours !== null ? round($avgCutHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время с распила')
                ->descriptionIcon('heroicon-m-scissors')
                ->color('warning'),

            Stat::make('Среднее время покраски', $avgPaintedHours !== null ? round($avgPaintedHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время с покраски')
                ->descriptionIcon('heroicon-m-paint-brush')
                ->color('success'),

            Stat::make('Среднее время упаковки', $avgPackedHours !== null ? round($avgPackedHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время с упаковки')
                ->descriptionIcon('heroicon-m-archive-box')
                ->color('info'),

            Stat::make('Среднее время поклейки', $avgSwornHours !== null ? round($avgSwornHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время с поклейки')
                ->descriptionIcon('heroicon-m-document-plus')
                ->color('indigo'),

            Stat::make('Переделка стекла', $avgGlassReworkHours !== null ? round($avgGlassReworkHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время переделки')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('danger'),

            Stat::make('Рекламация стекла', $avgGlassComplaintHours !== null ? round($avgGlassComplaintHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время рекламации')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),

            Stat::make('Готовность стекла', $avgGlassReadyHours !== null ? round($avgGlassReadyHours, 1) . 'ч' : 'Н/Д')
                ->description('Среднее время готовности')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Завершение заказа', $avgCompletedHours !== null ? round($avgCompletedHours, 1) . 'ч' : 'Н/Д')
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
                $hoursAgo = $date->diffInHours(now());
                $totalHours += $hoursAgo;
                $count++;
            }
        }

        return $count > 0 ? $totalHours / $count : null;
    }
} 