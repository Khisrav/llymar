<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    
    public function getTitle(): string | Htmlable
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        
        if (!$user) return 'Пользователи';
        
        return match (true) {
            $user->hasRole('Super-Admin') => 'Пользователи',
            $user->hasRole('Operator') => 'Менеджеры',
            $user->hasRole('Manager') => 'Дилеры',
            default => 'Пользователи'
        };
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
