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
                ->label('Спецификация')
                ->url(fn (Order $record) => route('orders.list_pdf', $record->id))
                ->openUrlInNewTab()
                ->color('gray')
                ->icon('heroicon-o-arrow-down-tray'),
            Actions\Action::make('sketcher')
                        ->label('Чертеж')
                        ->url(fn (Order $record) => route('app.sketcher', $record->id))
                        ->openUrlInNewTab()
                        ->color('gray')
                        ->icon('heroicon-o-pencil-square')
                        ->visible(
                            /**
                             * @return bool
                             */
                            function () {
                                $user = \Illuminate\Support\Facades\Auth::user();
                                /** @var \App\Models\User|null $user */
                                return $user && ($user->can('access app sketcher') || $user->hasRole('Super-Admin'));
                            }
                        ),
            Actions\Action::make('invoice_pdf')
                ->label('Счет PDF')
                ->url(fn (Order $record) => route('orders.download_bill', ['order' => $record->id]))
                ->openUrlInNewTab()
                ->disabled(fn (Order $record) => empty($record->invoice_id))
                ->color('gray')
                ->icon('heroicon-o-document-currency-dollar'),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['order_number'] = $data['order_number'] . '-' . $this->record->id;
        //if is_completed is true, set status to 'Завершен'
        if ($this->record->is_completed && str_starts_with($this->record->order_number, '1-')) {
            $data['status'] = 'sent';
        } else if ($this->record->is_completed && (str_starts_with($this->record->order_number, '4-') || str_starts_with($this->record->order_number, '6-'))) {
            $data['status'] = 'completed';
        }
        
        if ($this->record->status === 'created' && $data['status'] === 'paid') {
            $data['when_started_working_on_it'] = now();
        }
        
        return $data;
    }
}
