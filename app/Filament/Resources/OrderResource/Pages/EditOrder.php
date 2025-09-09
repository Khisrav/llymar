<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
// use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    
    protected static ?string $title = 'Просмотр заказа';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation(),
            Actions\Action::make('list_pdf')
                ->label('Перечень PDF')
                ->url(fn (Order $record) => route('orders.list_pdf', $record->id))
                ->openUrlInNewTab()
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\Action::make('invoice_pdf')
                ->label('Счет PDF')
                ->url(fn (Order $record) => 'https://enter.tochka.com/uapi/invoice/v1.0/bills/{customerCode}/' . $record->invoice_id . '/file')
                ->openUrlInNewTab()
                ->color('gray')
                ->icon('heroicon-o-document-currency-dollar'),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['order_number'] = $data['order_number'] . '-' . $this->record->id;
        return $data;
    }
}
