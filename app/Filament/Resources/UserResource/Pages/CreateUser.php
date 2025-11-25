<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
    
    public function getTitle(): string | Htmlable
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) return 'Создать пользователя';
        
        return match (true) {
            $user->hasRole('Operator') => 'Создать дилера',
            $user->hasRole('Dealer') || $user->hasRole('ROP') => 'Создать менеджера',
            default => 'Создать пользователя'
        };
    }
    
    protected function afterCreate(): void
    {
        /** @var \App\Models\User|null $parentUser */
        $parentUser = Auth::user();
        /** @var \App\Models\User $user */
        $user = $this->record;
        
        if (!$parentUser) return;
        
        if ($parentUser->hasRole('Dealer')) {
            $user->assignRole('Manager');
        } else if ($parentUser->hasRole('ROP')) {
            $user->assignRole('Dealer');
        }
        
        if ($parentUser->hasRole('Dealer') && $parentUser->can('access dxf')) {
            $user->givePermissionTo('access dxf');
        }
    }
}
