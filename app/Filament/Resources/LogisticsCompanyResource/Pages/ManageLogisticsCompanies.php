<?php

namespace App\Filament\Resources\LogisticsCompanyResource\Pages;

use App\Filament\Resources\LogisticsCompanyResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLogisticsCompanies extends ManageRecords
{
    protected static string $resource = LogisticsCompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
