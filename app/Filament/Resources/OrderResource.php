<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OpeningsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Contract;
use App\Models\Order;
use App\Models\User;
use Filament\Actions\DeleteAction;
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
use Filament\Tables\Actions\DeleteAction as ActionsDeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Заказы';
    
    public static function getEloquentQuery(): Builder
    {
        $parent_user = auth()->user();
        $child_users = User::where('parent_id', $parent_user->id)->pluck('id')->toArray();
    
        if ($parent_user->hasRole('Super-Admin') || $parent_user->hasRole('Workman')) {
            return parent::getEloquentQuery();
        }
    
        // Include only records that have child user IDs
        return parent::getEloquentQuery()->whereIn('user_id', $child_users);
    }

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
                                    ->label('ID')
                                    ->disabled()
                                    ->columnSpan(2),
            
                                TextInput::make('order_number')
                                    ->label('Номер заказа')
                                    ->disabled()
                                    ->columnSpan(2),
                                    
                                TextInput::make('ral_code')
                                    ->label('Цвет RAL')
                                    ->columnSpan(2),
                                    
                                TextInput::make('selected_factor')
                                    ->label('Коэффициент')
                                    ->disabled()
                                    ->columnSpan(2),
                                    
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
                                        'unknown' => 'Неизвестно',
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
                                    ->columnSpan(6), 
            
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
        $user = auth()->user();
        if ($user->hasRole('Super-Admin') || $user->hasRole('Operator') || $user->hasRole('Workman')) {
            $StatusColumn = SelectColumn::make('status')
                ->label('Статус')
                ->sortable()
                ->searchable()
                ->options([
                    'created' => 'Создан',
                    'paid' => 'Оплачен',
                    'expired' => 'Просрочен',
                    'assembled' => 'Собран',
                    'sent' => 'Отправлен',
                    'archived' => 'Архивирован',
                    'completed' => 'Завершен',
                    'unknown' => 'Неизвестно',
                ])
                ->toggleable(isToggledHiddenByDefault: false);
        } else {
            $StatusColumn = TextColumn::make('status')
                ->label('Статус')
                ->badge()
                ->color(fn (string $state): string => match($state) {
                    'created' => 'gray', 
                    'paid' => 'green',         
                    'expired' => 'red',        
                    'assembled' => 'teal',    
                    'sent' => 'indigo',        
                    'completed' => 'emerald', 
                    'archived' => 'yellow',   
                    'unknown' => 'fuchsia',  
                    default => 'info',
                })
                ->formatStateUsing(fn (string $state): string => match($state) {
                    'created' => 'Создан',
                    'paid' => 'Оплачен',
                    'expired' => 'Не оплачен',
                    'assembled' => 'Собран',
                    'sent' => 'Отправлен',
                    'completed' => 'Завершен',
                    'archived' => 'Архивирован',
                    'unknown' => 'Претензия ТК',
                    default => $state,
                })
                ->toggleable(isToggledHiddenByDefault: false);
        }
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                if (auth()->user()->hasRole('Workman')) {
                    return $query->whereIn('status', ['assembled', 'paid']);
                }
                
                return $query;
            })
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
                TextColumn::make('user.name')
                    ->label('ФИО пользователя')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('user.phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('ral_code')
                    ->label('Цвет')
                    ->sortable()
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
                $StatusColumn,
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->native(false)
                    ->options([
                        'created' => 'Создан',
                        'paid' => 'Оплачен',
                        'expired' => 'Не оплачен',
                        'assembled' => 'Собран',
                        'sent' => 'Отправлен',
                        'completed' => 'Выполнен',
                        'archived' => 'Архивирован',
                        'unknown' => 'Претензия ТК',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    EditAction::make(),
                    Action::make('list_pdf')
                        ->label('Спецификация')
                        ->url(fn (Order $record) => route('orders.list_pdf', $record->id))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-arrow-down-tray'),
                    Action::make('create_contract')
                        ->label('Создать Договор')
                        ->url(fn (Order $record) => route('filament.admin.resources.contracts.create', ['order_id' => $record->id, 'order_price' => $record->total_price]))
                        ->icon('heroicon-o-document-text')
                        ->visible(fn (Order $record) => $record->contracts()->count() === 0),
                    Action::make('view_contract')
                        ->label('Просмотр Договора')
                        ->url(fn (Order $record) => route('filament.admin.resources.contracts.edit', ['record' => $record->contracts()->first()->id]))
                        ->icon('heroicon-o-document-text')
                        ->visible(fn (Order $record) => $record->contracts()->count() > 0),
                    Action::make('invoice_pdf')
                        ->label('Счет PDF')
                        ->url(fn (Order $record) => 'https://enter.tochka.com/uapi/invoice/v1.0/bills/{customerCode}/' . $record->invoice_id . '/file')
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-currency-dollar'),
                    ActionsDeleteAction::make()
                        ->requiresConfirmation()
                        ->hidden(!$user->hasRole('Super-Admin')),
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
            // 'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'created')->count();
    }
}
