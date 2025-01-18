<?php

namespace App\Filament\Resources\WholesaleFactorResource\Pages;

use App\Filament\Resources\WholesaleFactorResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditWholesaleFactor extends EditRecord
{
    protected static string $resource = WholesaleFactorResource::class;
    
    protected static ?string $title = 'Просмотр коэффициента';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
