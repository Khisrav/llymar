<?php

namespace App\Filament\Resources\OrderJournalResource\Pages;

use App\Filament\Resources\OrderJournalResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderJournal extends CreateRecord
{
    protected static string $resource = OrderJournalResource::class;
}
