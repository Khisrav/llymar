<?php

namespace App\Filament\Resources\WholesaleFactorResource\Pages;

use App\Filament\Resources\WholesaleFactorResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWholesaleFactor extends CreateRecord
{
    protected static string $resource = WholesaleFactorResource::class;
    
    protected static ?string $title = 'Создать оптовый коэффициент';
}
