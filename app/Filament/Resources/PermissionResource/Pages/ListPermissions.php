<?php

namespace App\Filament\Resources\PermissionResource\Pages;

use App\Filament\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('matrix')
                ->label('Матрица разрешений')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->url(fn () => \App\Filament\Resources\RoleResource::getUrl('matrix')),
            // Actions\CreateAction::make(),
        ];
    }
}
