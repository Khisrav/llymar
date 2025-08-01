<?php

namespace App\Filament\Resources\UserResource\RelationManagers\Widgets;

use App\Models\CommissionCredit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommissionCreditsOverviewByUser extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = $this->ownerRecord?->id;
        
        if (!$userId) {
            return [];
        }

        // Calculate accruals (начисления)
        $totalAccruals = CommissionCredit::where('parent_id', $userId)
            ->where('type', 'accrual')
            ->sum('amount');

        // Calculate write-offs (списания/выплаты)
        $totalWriteOffs = CommissionCredit::where('parent_id', $userId)
            ->where('type', 'write-off')
            ->sum('amount');

        // Calculate difference (остаток)
        $difference = $totalAccruals - $totalWriteOffs;

        return [
            Stat::make('Total Accruals', '₽' . number_format($totalAccruals, 2))
                ->description('Total commission accruals')
                ->descriptionIcon('heroicon-o-plus-circle')
                ->color('success'),
            
            Stat::make('Total Write-offs', '₽' . number_format($totalWriteOffs, 2))
                ->description('Total commission payments')
                ->descriptionIcon('heroicon-o-minus-circle')
                ->color('warning'),
            
            Stat::make('Balance', '₽' . number_format($difference, 2))
                ->description('Remaining commission balance')
                ->descriptionIcon('heroicon-o-calculator')
                ->color($difference >= 0 ? 'success' : 'danger'),
        ];
    }
}
