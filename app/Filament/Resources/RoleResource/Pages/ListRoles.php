<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('matrix')
                ->label('Матрица разрешений')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->url(RoleResource::getUrl('matrix')),
            Actions\CreateAction::make(),
        ];
    }
}
