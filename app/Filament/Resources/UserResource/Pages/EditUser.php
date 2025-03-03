<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;
    
    public function getTitle(): string | Htmlable
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super-Admin')) return 'Просмотр пользователя';
        else if ($user->hasRole('Operator')) return 'Просмотр менеджера';
        else if ($user->hasRole('Manager')) return 'Просмотр дилера';
        
        return 'Просмотр пользователя';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
