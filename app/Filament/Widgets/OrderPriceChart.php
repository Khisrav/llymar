<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class OrderPriceChart extends ChartWidget
{
    protected static ?string $heading = 'Стоимость заказов по времени';
    
    protected int | string | array $columnSpan = 'full';
    
    public ?string $filter = 'individual';

    protected function getData(): array
    {
        if ($this->filter === 'monthly') {
            return $this->getMonthlyAggregatedData();
        }
        
        return $this->getIndividualOrdersData();
    }
    
    protected function getMonthlyAggregatedData(): array
    {
        // Get orders from the last 12 months
        $orders = Order::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(total_price) as total')
            ->where('created_at', '>=', now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Create arrays for labels and data
        $labels = [];
        $data = [];
        
        // Fill in missing months with 0 values
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthName = now()->subMonths($i)->format('M Y');
            
            $orderForMonth = $orders->firstWhere('month', $month);
            
            $labels[] = $monthName;
            $data[] = $orderForMonth ? (int)$orderForMonth->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Стоимость всех заказов по месяцам',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }
    
    protected function getIndividualOrdersData(): array
    {
        // Get recent orders (last 50 orders for performance)
        $orders = Order::latest()
            ->take(50)
            ->get()
            ->reverse(); // Show chronologically

        $labels = [];
        $data = [];
        
        foreach ($orders as $order) {
            $labels[] = $order->created_at->format('m.Y');
            $data[] = (int)$order->total_price;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Стоимость отдельных заказов',
                    'data' => $data,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'monthly' => 'По месяцам',
            'individual' => 'Отдельные заказы',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
    
    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Цена (₽)',
                    ],
                    'ticks' => [
                        'callback' => 'function(value) { return value.toLocaleString("ru-RU") + " ₽"; }',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Дата',
                    ],
                ],
            ],
            'plugins' => [
                'tooltip' => [
                    'mode' => 'index',
                    'intersect' => false,
                    'callbacks' => [
                        'label' => 'function(context) { return context.dataset.label + ": " + context.raw.toLocaleString("ru-RU") + " ₽"; }',
                    ],
                ],
            ],
        ];
    }
} 