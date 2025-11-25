<?php

namespace App\Filament\Resources\CommissionCreditResource\Pages;

use App\Filament\Resources\CommissionCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ViewCommissionCredit extends ViewRecord
{
    protected static string $resource = CommissionCreditResource::class;

    public function getTitle(): string | Htmlable
    {
        $type = $this->record->type === 'write-off' ? 'выплаты' : 'начисления';
        return "Просмотр {$type} комиссии";
    }

    protected function getHeaderActions(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $canEdit = $user && $user->hasRole('Super-Admin') && $this->record->type === 'write-off';

        return [
            Actions\EditAction::make()
                ->visible($canEdit)
                ->label('Редактировать')
                ->modalWidth('2xl'),
        ];
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full';
    }
} 