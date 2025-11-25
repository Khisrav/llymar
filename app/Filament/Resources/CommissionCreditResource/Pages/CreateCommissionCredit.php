<?php

namespace App\Filament\Resources\CommissionCreditResource\Pages;

use App\Filament\Resources\CommissionCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class CreateCommissionCredit extends CreateRecord
{
    protected static string $resource = CommissionCreditResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Добавить выплату комиссии';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Выплата комиссии успешно добавлена';
    }

    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('Super-Admin')) {
            $this->halt(403, 'У вас нет прав для создания комиссионных операций');
        }
    }
} 