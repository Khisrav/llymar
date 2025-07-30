<?php

namespace App\Filament\Resources\CommissionCreditResource\Pages;

use App\Filament\Resources\CommissionCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommissionCredit extends ViewRecord
{
    protected static string $resource = CommissionCreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
} 