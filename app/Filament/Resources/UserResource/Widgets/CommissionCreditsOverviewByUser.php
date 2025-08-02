<?php

namespace App\Filament\Resources\UserResource\Widgets;

use App\Models\CommissionCredit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class CommissionCreditsOverviewByUser extends BaseWidget
{
    public ?User $record = null;

    protected function getStats(): array
    {
        // Get the user record being viewed
        $user = $this->record;
        
        // If no record is set, don't show any stats
        if (!$user) {
            return [];
        }
        
        // Check if current authenticated user can view this data
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        if (!$currentUser) {
            return [];
        }
        
        // Super admins can see all data, others can only see their own or their children's data
        if (!$currentUser->hasRole('Super-Admin')) {
            $canView = $currentUser->id === $user->id || 
                      $currentUser->id === $user->parent_id ||
                      $user->parent_id === $currentUser->id;
            
            if (!$canView) {
                return [];
            }
        }
        
        // Build the query for this specific user's commission credits
        $query = CommissionCredit::where('parent_id', $user->id);
        
        // Calculate accrual amount (money earned)
        $accrualAmount = $query->clone()->where('type', 'accrual')->sum('amount');
        
        // Calculate write-off amount (money withdrawn)
        $writeOffAmount = $query->clone()->where('type', 'write-off')->sum('amount');
        
        // Calculate current balance (accrual - write-off)
        $currentBalance = $accrualAmount - $writeOffAmount;
        
        // Get count of transactions
        $totalTransactions = $query->count();
        $accrualCount = $query->clone()->where('type', 'accrual')->count();
        $writeOffCount = $query->clone()->where('type', 'write-off')->count();
        
        return [
            Stat::make('Общий заработок', $this->formatCurrency($accrualAmount))
                ->description($accrualCount > 0 ? "Операций начисления: {$accrualCount}" : 'Нет начислений')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart($this->getChartData($user->id, 'accrual')),
                
            Stat::make('Выведено средств', $this->formatCurrency($writeOffAmount))
                ->description($writeOffCount > 0 ? "Операций вывода: {$writeOffCount}" : 'Нет выводов')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart($this->getChartData($user->id, 'write-off')),
                
            Stat::make('Текущий баланс', $this->formatCurrency($currentBalance))
                ->description($this->getBalanceDescription($currentBalance, $totalTransactions))
                ->descriptionIcon($this->getBalanceIcon($currentBalance))
                ->color($this->getBalanceColor($currentBalance)),
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
            return 'Идеальный баланс';
        }
        
        if ($balance > 0) {
            return 'Доступно к выводу';
        }
        
        return 'Отрицательный баланс';
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
            return 'heroicon-m-banknotes';
        }
        
        return 'heroicon-m-exclamation-triangle';
    }
    
    /**
     * Get appropriate color for balance status
     */
    private function getBalanceColor(float $balance): string
    {
        if ($balance === 0.0) {
            return 'primary';
        }
        
        if ($balance > 0) {
            return 'success';
        }
        
        return 'warning';
    }
    
    /**
     * Get chart data for the last 6 months
     */
    private function getChartData(int $userId, string $type): array
    {
        $data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $amount = CommissionCredit::where('parent_id', $userId)
                ->where('type', $type)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('amount');
            
            $data[] = round($amount, 2);
        }
        
        return $data;
    }
}
