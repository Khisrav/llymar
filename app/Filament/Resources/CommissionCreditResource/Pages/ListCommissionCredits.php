<?php

namespace App\Filament\Resources\CommissionCreditResource\Pages;

use App\Filament\Resources\CommissionCreditResource;
use App\Filament\Resources\CommissionCreditResource\Widgets\CommissionCreditsOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ListCommissionCredits extends ListRecords
{
    protected static string $resource = CommissionCreditResource::class;

    public function getTitle(): string | Htmlable
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) return 'Комиссионные операции';
        
        return match (true) {
            $user->hasRole('Super-Admin') => 'Все комиссионные операции',
            $user->hasRole('ROP') => 'Мои комиссионные операции',
            default => 'Комиссионные операции'
        };
    }

    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $isSuperAdmin = $user && $user->hasRole('Super-Admin');

        return [
            Actions\CreateAction::make()
                ->visible($isSuperAdmin)
                ->label('Добавить выплату')
                ->icon('heroicon-o-plus')
                ->modalHeading('Добавить выплату комиссии')
                ->modalDescription('Создание записи о выплате комиссионных средств')
                ->modalWidth('2xl'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CommissionCreditsOverview::class,
        ];
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
} 