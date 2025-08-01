<?php

namespace App\Filament\Resources\CommissionCreditResource\Pages;

use App\Filament\Resources\CommissionCreditResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommissionCredit extends EditRecord
{
    protected static string $resource = CommissionCreditResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
} 