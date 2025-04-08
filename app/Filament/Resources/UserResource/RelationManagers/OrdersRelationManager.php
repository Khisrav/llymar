<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('order_number')
                    ->label('№')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_name')
                    ->label('ФИО клиента')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('total_price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'created' => 'info',
                        'paid' => 'primary',
                        'expired' => 'danger',
                        'assembled' => 'gray',
                        'sent' => 'warning',
                        'completed' => 'success',
                        'archived' => 'gray',
                        'unknown' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'created' => 'Создан',
                        'paid' => 'Оплачен',
                        'expired' => 'Просрочен',
                        'assembled' => 'Собран',
                        'sent' => 'Отправлен',
                        'completed' => 'Завершен',
                        'archived' => 'Архивирован',
                        'unknown' => 'Неизвестно',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'created' => 'Создан',
                        'paid' => 'Оплачен',
                        'expired' => 'Просрочен',
                        'assembled' => 'Собран',
                        'sent' => 'Отправлен',
                        'completed' => 'Выполнен',
                        'archived' => 'Архивирован',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('view')
                    ->label('Просмотр')
                    ->url(fn (Order $record) => route(auth()->user()->can('update order') ? 'filament.admin.resources.orders.edit' : 'filament.admin.resources.orders.view', $record->id))
                    // ->openUrlInNewTab()
                    ->icon('heroicon-o-eye'),
                ActionGroup::make([
                    Action::make('list_pdf')
                        ->label('Перечень PDF')
                        ->url(fn (Order $record) => route('orders.list_pdf', $record->id))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-down-tray'),
                    Action::make('invoice_pdf')
                        ->label('Счет PDF')
                        ->url(fn (Order $record) => 'https://enter.tochka.com/uapi/invoice/v1.0/bills/{customerCode}/' . $record->invoice_id . '/file')
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-currency-dollar'),
                    ActionsDeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
