<?php

namespace App\Filament\Resources\CommissionCreditResource\Widgets;

use App\Models\CommissionCredit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommissionCreditsOverview extends BaseWidget
{
    /**
     * Get the current authenticated user with proper typing
     */
    protected function getCurrentUser(): ?User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Check if current user is Super Admin
     */
    protected function isSuperAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->hasRole('Super-Admin');
    }

    protected function getStats(): array
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return [];
        }

        // Build base query with proper scoping
        $baseQuery = CommissionCredit::query();
        
        if (!$user->hasRole('Super-Admin')) {
            $baseQuery->where(function ($query) use ($user) {
                $query->where('parent_id', $user->id)
                      ->orWhere('user_id', $user->id);
            });
        }

        // Calculate totals
        $totalAccruals = $baseQuery->clone()->where('type', 'accrual')->sum('amount');
        $totalWriteOffs = $baseQuery->clone()->where('type', 'write-off')->sum('amount');
        $totalBalance = $totalAccruals - $totalWriteOffs;

        // Get transaction counts
        $accrualCount = $baseQuery->clone()->where('type', 'accrual')->count();
        $writeOffCount = $baseQuery->clone()->where('type', 'write-off')->count();
        $totalTransactions = $accrualCount + $writeOffCount;

        // Get monthly stats for current year
        $currentYear = now()->year;
        $monthlyAccruals = $baseQuery->clone()
            ->where('type', 'accrual')
            ->whereYear('created_at', $currentYear)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyWriteOffs = $baseQuery->clone()
            ->where('type', 'write-off')
            ->whereYear('created_at', $currentYear)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return [
            Stat::make('Общие начисления', $this->formatCurrency($totalAccruals))
                ->description($accrualCount > 0 ? "Операций: {$accrualCount}" : 'Нет начислений')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getMonthlyChartData($monthlyAccruals)),

            Stat::make('Общие выплаты', $this->formatCurrency($totalWriteOffs))
                ->description($writeOffCount > 0 ? "Операций: {$writeOffCount}" : 'Нет выплат')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('warning')
                ->chart($this->getMonthlyChartData($monthlyWriteOffs)),

            Stat::make('Общий баланс', $this->formatCurrency($totalBalance))
                ->description($this->getBalanceDescription($totalBalance, $totalTransactions))
                ->descriptionIcon($this->getBalanceIcon($totalBalance))
                ->color($this->getBalanceColor($totalBalance)),

            // Additional stat for Super Admins
            ...$this->getAdditionalStats(),
        ];
    }

    /**
     * Get additional statistics for Super Admins
     */
    protected function getAdditionalStats(): array
    {
        if (!$this->isSuperAdmin()) {
            return [];
        }

        // Get pending balance (users with positive balance)
        $pendingBalance = DB::table('commission_credits')
            ->select('parent_id')
            ->selectRaw('SUM(CASE WHEN type = "accrual" THEN amount ELSE -amount END) as balance')
            ->groupBy('parent_id')
            ->havingRaw('balance > 0')
            ->sum('balance');

        $usersWithBalance = DB::table('commission_credits')
            ->select('parent_id')
            ->selectRaw('SUM(CASE WHEN type = "accrual" THEN amount ELSE -amount END) as balance')
            ->groupBy('parent_id')
            ->havingRaw('balance > 0')
            ->count();

        return [
            Stat::make('К выплате', $this->formatCurrency($pendingBalance))
                ->description($usersWithBalance > 0 ? "Пользователей: {$usersWithBalance}" : 'Нет долгов')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($pendingBalance > 0 ? 'danger' : 'success'),
        ];
    }

    /**
     * Format currency with proper Russian formatting
     */
    private function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, ',', ' ') . ' ₽';
    }

    /**
     * Get balance description based on amount and transaction count
     */
    private function getBalanceDescription(float $balance, int $totalTransactions): string
    {
        if ($totalTransactions === 0) {
            return 'Нет операций';
        }

        if ($balance === 0.0) {
            return 'Сбалансированная система';
        }

        if ($balance > 0) {
            return 'Долг к выплате';
        }

        return 'Переплата по системе';
    }

    /**
     * Get appropriate icon for balance status
     */
    private function getBalanceIcon(float $balance): string
    {
        if ($balance === 0.0) {
            return 'heroicon-m-check-badge';
        }

        if ($balance > 0) {
            return 'heroicon-m-exclamation-triangle';
        }

        return 'heroicon-m-information-circle';
    }

    /**
     * Get appropriate color for balance status
     */
    private function getBalanceColor(float $balance): string
    {
        if ($balance === 0.0) {
            return 'success';
        }

        if ($balance > 0) {
            return 'warning';
        }

        return 'info';
    }

    /**
     * Get chart data for the last 12 months
     */
    private function getMonthlyChartData(array $monthlyData): array
    {
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->month;
            $data[] = round($monthlyData[$month] ?? 0, 2);
        }
        
        return $data;
    }
} 