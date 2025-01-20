<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OpeningsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Заказы';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Информация о заказе')
                    ->collapsible()
                    ->schema([
                        Grid::make(12) 
                            ->schema([
                                TextInput::make('id')
                                    ->label('ID заказа')
                                    ->disabled()
                                    ->columnSpan(3),
            
                                TextInput::make('order_number')
                                    ->label('Номер заказа')
                                    ->disabled()
                                    ->columnSpan(3),
                                    
                                TextInput::make('user_id')
                                    ->label('Кем создан')
                                    ->disabled()
                                    ->formatStateUsing(fn (string $state) => 'ID: ' . $state . ' - ' . User::find($state)->name)
                                    ->columnSpan(3),
                                
                                TextInput::make('customer_name')
                                    ->label('ФИО клиента')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(3),
            
                                TextInput::make('customer_phone')
                                    ->label('Номер телефона')
                                    ->required()
                                    ->mask('+7 (999) 999 99-99')
                                    ->columnSpan(3),
                                    
                                TextInput::make('customer_email')
                                    ->label('Email')
                                    ->required()
                                    ->columnSpan(3),
            
                                Select::make('status')
                                    ->label('Статус заказа')
                                    ->required()
                                    ->native(false)
                                    ->options([
                                        'created' => 'Создан',
                                        'paid' => 'Оплачен',
                                        'expired' => 'Просрочен',
                                        'assembled' => 'Собран',
                                        'sent' => 'Отправлен',
                                        'completed' => 'Выполнен',
                                        'archived' => 'Архивирован',
                                    ])
                                    ->columnSpan(3),
            
                                TextInput::make('total_price')
                                    ->label('Итоговая стоимость')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₽')
                                    ->columnSpan(3),
            
                                TextInput::make('customer_address')
                                    ->label('Адрес')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(6), // Full width
            
                                Textarea::make('comment')
                                    ->label('Комментарий заказчика')
                                    ->rows(3)
                                    ->maxLength(500)
                                    ->columnSpan(6),
                            ]),
                    ])
            ]);
    }



    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
                        'paid' => 'danger',
                        'expired' => 'gray',
                        'assembled' => 'primary',
                        'sent' => 'success',
                        'completed' => 'warning',
                        'archived' => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'created' => 'Создан',
                        'paid' => 'Оплачен',
                        'expired' => 'Просрочен',
                        'assembled' => 'Собран',
                        'sent' => 'Отправлен',
                        'completed' => 'Завершен',
                        'archived' => 'Архивирован',
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
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OpeningsRelationManager::class,
            OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
