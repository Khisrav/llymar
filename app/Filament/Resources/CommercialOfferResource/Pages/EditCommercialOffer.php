<?php

namespace App\Filament\Resources\CommercialOfferResource\Pages;

use App\Filament\Resources\CommercialOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCommercialOffer extends EditRecord
{
    protected static string $resource = CommercialOfferResource::class;

    protected static ?string $title = 'Редактирование коммерческого предложения';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Просмотр'),
            Actions\DeleteAction::make()
                ->label('Удалить'),
            Actions\Action::make('download_pdf')
                ->label('Скачать PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->url(fn (): string => 
                    route('orders.commercial_offer_pdf', [
                        'customer' => [
                            'name' => $this->record->customer_name,
                            'phone' => $this->record->customer_phone,
                            'address' => $this->record->customer_address,
                            'comment' => $this->record->customer_comment,
                        ],
                        'manufacturer' => [
                            'name' => $this->record->manufacturer_name,
                            'phone' => $this->record->manufacturer_phone,
                        ],
                        'openings' => $this->record->openings,
                        'additional_items' => $this->record->additional_items,
                        'glass' => $this->record->glass,
                        'services' => $this->record->services,
                        'cart_items' => $this->record->cart_items,
                        'total_price' => $this->record->total_price,
                        'markup_percentage' => $this->record->markup_percentage,
                        'selected_factor' => $this->record->selected_factor,
                    ])
                )
                ->openUrlInNewTab(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
