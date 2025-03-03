<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    public function getTitle(): string | Htmlable
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super-Admin')) return 'Создать пользователя';
        else if ($user->hasRole('Operator')) return 'Создать менеджера';
        else if ($user->hasRole('Manager')) return 'Создать дилера';
        
        return 'Создать пользователя';
    }
    
    protected function afterCreate(): void
    {
        $parent_user = auth()->user();
        $user = $this->record;
        
        if ($parent_user->hasRole('Operator')) {
            $user->assignRole('Manager');
        } else if ($parent_user->hasRole('Manager')) {
            $user->assignRole('Dealer');
        }
    }
}
