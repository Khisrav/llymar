<?php

namespace App\Filament\Resources\CommercialOfferResource\Pages;

use App\Filament\Resources\CommercialOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCommercialOffer extends ViewRecord
{
    protected static string $resource = CommercialOfferResource::class;
    
    protected static ?string $title = 'Просмотр коммерческого предложения';

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make()
            //     ->label('Редактировать'),
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()->hasRole('Super-Admin'))
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
} 