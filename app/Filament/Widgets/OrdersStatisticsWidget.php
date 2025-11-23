<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdersStatisticsWidget extends BaseWidget
{
    /**
     * Check if current user is Super Admin
     */
    protected function isSuperAdmin(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user && $user->hasRole('Super-Admin');
    }

    protected function getStats(): array
    {
        // Only show stats to Super Admin
        if (!$this->isSuperAdmin()) {
            return [];
        }

        // Get current year and month for comparison
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $previousMonth = now()->subMonth();

        // Finished orders are those with 'completed' or 'paid' status
        $finishedStatuses = ['completed', 'paid'];

        // Total finished orders count
        $totalFinishedOrders = Order::whereIn('status', $finishedStatuses)->count();
        
        // Finished orders this month
        $finishedOrdersThisMonth = Order::whereIn('status', $finishedStatuses)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->count();

        // Finished orders last month for comparison
        $finishedOrdersLastMonth = Order::whereIn('status', $finishedStatuses)
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->count();

        // Calculate percentage change
        $monthlyChange = 0;
        if ($finishedOrdersLastMonth > 0) {
            $monthlyChange = (($finishedOrdersThisMonth - $finishedOrdersLastMonth) / $finishedOrdersLastMonth) * 100;
        } elseif ($finishedOrdersThisMonth > 0) {
            $monthlyChange = 100;
        }

        // Total revenue from finished orders only
        $totalRevenue = Order::whereIn('status', $finishedStatuses)
            ->whereNotNull('total_price')
            ->sum('total_price');

        // Revenue this month from finished orders
        $revenueThisMonth = Order::whereIn('status', $finishedStatuses)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->whereNotNull('total_price')
            ->sum('total_price');

        // Revenue last month from finished orders
        $revenueLastMonth = Order::whereIn('status', $finishedStatuses)
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->whereNotNull('total_price')
            ->sum('total_price');

        // Calculate revenue percentage change
        $revenueChange = 0;
        if ($revenueLastMonth > 0) {
            $revenueChange = (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100;
        } elseif ($revenueThisMonth > 0) {
            $revenueChange = 100;
        }

        // Order statuses distribution
        $statusCounts = Order::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $completedOrders = $statusCounts['completed'] ?? 0;
        $paidOrders = $statusCounts['paid'] ?? 0;
        $pendingOrders = $statusCounts['pending'] ?? 0;
        $expiredOrders = $statusCounts['expired'] ?? 0;

        // Get monthly finished orders data for chart (last 12 months)
        $monthlyFinishedOrderData = $this->getMonthlyFinishedOrderData();
        
        // Get monthly revenue data for chart (last 12 months)
        $monthlyRevenueData = $this->getMonthlyRevenueData();

        return [
            Stat::make('Завершенных заказов', number_format($totalFinishedOrders))
                ->description($this->getOrdersChangeDescription($monthlyChange))
                ->descriptionIcon($monthlyChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($monthlyChange >= 0 ? 'success' : 'danger')
                ->chart($monthlyFinishedOrderData),

            Stat::make('Выручка с завершенных', $this->formatCurrency($totalRevenue))
                ->description($this->getRevenueChangeDescription($revenueChange))
                ->descriptionIcon($revenueChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($revenueChange >= 0 ? 'success' : 'danger')
                ->chart($monthlyRevenueData),

            Stat::make('Выручка за месяц', $this->formatCurrency($revenueThisMonth))
                ->description("Прошлый месяц: " . $this->formatCurrency($revenueLastMonth))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Статистика заказов', number_format($completedOrders + $paidOrders) . ' / ' . number_format($completedOrders + $paidOrders + $pendingOrders + $expiredOrders))
                ->description("Завершено: {$completedOrders} | Оплачено: {$paidOrders} | Ожидают: {$pendingOrders} | Просрочено: {$expiredOrders}")
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
        ];
    }

    /**
     * Get orders change description
     */
    private function getOrdersChangeDescription(float $change): string
    {
        if ($change == 0) {
            return 'Без изменений за месяц';
        }

        $changeText = abs($change) >= 1 ? number_format(abs($change), 1) : '< 1';
        
        if ($change > 0) {
            return "+{$changeText}% за месяц";
        } else {
            return "-{$changeText}% за месяц";
        }
    }

    /**
     * Get revenue change description
     */
    private function getRevenueChangeDescription(float $change): string
    {
        if ($change == 0) {
            return 'Выручка без изменений';
        }

        $changeText = abs($change) >= 1 ? number_format(abs($change), 1) : '< 1';
        
        if ($change > 0) {
            return "Рост выручки +{$changeText}%";
        } else {
            return "Падение выручки -{$changeText}%";
        }
    }

    /**
     * Format currency with proper Russian formatting
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' ₽';
    }

    /**
     * Get monthly finished orders data for chart (last 12 months)
     */
    private function getMonthlyFinishedOrderData(): array
    {
        $data = [];
        $finishedStatuses = ['completed', 'paid'];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Order::whereIn('status', $finishedStatuses)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $data[] = $count;
        }
        
        return $data;
    }

    /**
     * Get monthly revenue data for chart (last 12 months)
     */
    private function getMonthlyRevenueData(): array
    {
        $data = [];
        $finishedStatuses = ['completed', 'paid'];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $revenue = Order::whereIn('status', $finishedStatuses)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->whereNotNull('total_price')
                ->sum('total_price');
            
            // Convert to thousands for better chart readability
            $data[] = round($revenue / 1000, 1);
        }
        
        return $data;
    }
} 