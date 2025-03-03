<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
    
    public function getTitle(): string | Htmlable
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super-Admin')) return ' Пользователи';
        else if ($user->hasRole('Operator')) return ' Менеджеры';
        else if ($user->hasRole('Manager')) return 'Дилеры';
        
        return 'Пользователи';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
