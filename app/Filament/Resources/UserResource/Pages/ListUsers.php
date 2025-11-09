<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Bostos\ReorderableColumns\Concerns\HasReorderableColumns;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class ListUsers extends ListRecords
{
    use HasReorderableColumns;
    
    protected static string $resource = UserResource::class;
    
    protected static string $view = 'filament.resources.user-resource.pages.list-users';
    
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
