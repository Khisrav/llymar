<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\UserResource\Widgets\CommissionCreditsOverviewByUser;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    
    public function getTitle(): string | Htmlable
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) return 'Просмотр пользователя';
        
        return match (true) {
            $user->hasRole('Super-Admin') => 'Просмотр пользователя',
            $user->hasRole('Operator') => 'Просмотр менеджера',
            $user->hasRole('Manager') => 'Просмотр дилера',
            default => 'Просмотр пользователя'
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CommissionCreditsOverviewByUser::make(['record' => $this->record]),
        ];
    }
}
