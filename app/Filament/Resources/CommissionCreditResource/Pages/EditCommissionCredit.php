<?php

namespace App\Filament\Resources\CommissionCreditResource\Pages;

use App\Filament\Resources\CommissionCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class EditCommissionCredit extends EditRecord
{
    protected static string $resource = CommissionCreditResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Редактировать выплату комиссии';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Просмотр')
                ->modalWidth('2xl'),
            Actions\DeleteAction::make()
                ->label('Удалить')
                ->requiresConfirmation()
                ->modalHeading('Удалить запись о выплате?')
                ->modalDescription('Это действие нельзя будет отменить.')
                ->successNotificationTitle('Запись о выплате удалена'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Выплата комиссии успешно обновлена';
    }

    protected function authorizeAccess(): void
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user || !$user->hasRole('Super-Admin')) {
            $this->halt(403, 'У вас нет прав для редактирования комиссионных операций');
        }

        // Only allow editing write-off records
        if ($this->record->type !== 'write-off') {
            $this->halt(403, 'Можно редактировать только записи о выплатах');
        }
    }
} 