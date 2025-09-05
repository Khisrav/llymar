<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers\OpeningsRelationManager;
use App\Filament\Resources\OrderResource\RelationManagers\OrderItemsRelationManager;
use App\Models\Order;
use App\Models\User;
use App\Services\TochkaBankService;
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
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';
    protected static ?string $navigationLabel = 'Заказы';
    protected static ?string $recordTitleAttribute = 'order_number';
    
    // Order status constants for consistency
    public const ORDER_STATUSES = [
        'created' => 'Создан',
        'paid' => 'Оплачен', 
        'expired' => 'Просрочен',
        'assembled' => 'Собран',
        'sent' => 'Отправлен',
        'completed' => 'Завершен',
        'archived' => 'Архивирован',
        'unknown' => 'Неизвестно'
    ];
    
    // Status colors for badges
    public const ORDER_STATUS_COLORS = [
        'created' => 'gray',
        'paid' => 'success',
        'expired' => 'danger',
        'assembled' => 'info',
        'sent' => 'warning',
        'completed' => 'success',
        'archived' => 'secondary',
        'unknown' => 'danger'
    ];
    
    // Invoice status constants
    public const INVOICE_STATUSES = [
        'payment_waiting' => 'Ожидание оплаты',
        'payment_expired' => 'Просрочен',
        'payment_paid' => 'Оплачен',
        'created' => 'Создан'
    ];
    
    // Invoice status colors
    public const INVOICE_STATUS_COLORS = [
        'payment_waiting' => 'info',
        'payment_expired' => 'danger',
        'payment_paid' => 'success',
        'created' => 'gray'
    ];
    
    // Role constants
    public const ROLES = [
        'SUPER_ADMIN' => 'Super-Admin',
        'MANAGER' => 'Manager',
        'OPERATOR' => 'Operator',
        'WORKMAN' => 'Workman',
        'AGENT' => 'Agent',
        'DEALER' => 'Dealer'
    ];

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        // Check user roles for admin access
        /** @var \App\Models\User $user */
        $hasAdminAccess = $user->hasRole(self::ROLES['SUPER_ADMIN']) || 
                         $user->hasRole(self::ROLES['WORKMAN']);
        
        if ($hasAdminAccess) {
            return parent::getEloquentQuery()->with(['user:id,name,phone']);
        }
        
        // Get child users for hierarchical access
        $childUserIds = Cache::remember(
            "child_users_{$user->id}",
            600, // 10 minutes
            fn() => User::where('parent_id', $user->id)->pluck('id')->toArray()
        );
        
        return parent::getEloquentQuery()
            ->with(['user:id,name,phone'])
            ->whereIn('user_id', $childUserIds);
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    // ->description('Основные данные заказа')
                    ->icon('heroicon-o-document-text')
                    ->collapsible()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('id')
                                    ->label('ID')
                                    ->disabled()
                                    ->columnSpan(1),
            
                                TextInput::make('order_number')
                                    ->label('№ заказа')
                                    ->disabled()
                                    ->columnSpan(1),
                                    
                                TextInput::make('ral_code')
                                    ->label('Цвет RAL')
                                    ->placeholder('RAL 9010')
                                    ->columnSpan(1),
                                    
                                TextInput::make('selected_factor')
                                    ->label('Коэффициент')
                                    ->disabled()
                                    // ->numeric()
                                    ->default('kz')
                                    ->columnSpan(1),
                            ]),
                        
                        Grid::make(3)
                            ->schema([
                                TextInput::make('user_id')
                                    ->label('Создатель заказа')
                                    ->disabled()
                                    ->formatStateUsing(function (string $state) {
                                        $user = User::find($state);
                                        return $user ? "#{$state} - {$user->name}" : "User #{$state}";
                                    })
                                    ->columnSpan(1),
                                
                                Select::make('status')
                                    ->label('Статус заказа')
                                    ->required()
                                    ->native(false)
                                    ->options(self::ORDER_STATUSES)
                                    ->columnSpan(1),
            
                                TextInput::make('total_price')
                                    ->label('Общая стоимость')
                                    ->required()
                                    ->numeric()
                                    ->prefix('₽')
                                    ->step(0.01)
                                    ->columnSpan(1),
                            ])
                    ]),
                    
                Section::make('Информация о клиенте')
                    // ->description('Контактные данные заказчика')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('customer_name')
                                    ->label('ФИО клиента')
                                    // ->required()
                                    ->maxLength(255)
                                    ->placeholder('Иванов Иван Иванович')
                                    ->columnSpan(1),

                                TextInput::make('customer_phone')
                                    ->label('Номер телефона')
                                    // ->required()
                                    // ->tel()
                                    ->mask('+7 (999) 999 99-99')
                                    ->placeholder('+7 (___) ___ __-__')
                                    ->columnSpan(1),
                            ]),
                            
                        Grid::make(2)
                            ->schema([
                                TextInput::make('customer_email')
                                    ->label('Email')
                                    ->email()
                                    ->placeholder('example@mail.com')
                                    ->columnSpan(1),

                                TextInput::make('customer_address')
                                    ->label('Адрес доставки')
                                    // ->required()
                                    ->maxLength(255)
                                    ->placeholder('г. Москва, ул. Примерная, д. 1')
                                    ->columnSpan(1),
                            ]),
                            
                        Textarea::make('comment')
                            ->label('Примечание')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Дополнительные пожелания или комментарии...')
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        $user = Auth::user();
        /** @var \App\Models\User|null $user */
        
        $hasEditAccess = $user && (
            $user->hasRole(self::ROLES['SUPER_ADMIN']) || 
            $user->hasRole(self::ROLES['OPERATOR']) || 
            $user->hasRole(self::ROLES['WORKMAN'])
        );

        return $table
            ->modifyQueryUsing(function (Builder $query) use ($user): Builder {
                if ($user && $user->hasRole(self::ROLES['WORKMAN'])) {
                    return $query->whereIn('status', ['assembled', 'paid']);
                }
                
                return $query;
            })
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('order_number')
                    ->label('№ заказа')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->weight('medium')
                    ->copyable()
                    ->copyMessage('Номер заказа скопирован!')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                TextColumn::make('user.name')
                    ->label('Менеджер')
                    ->formatStateUsing(function ($record) {
                        $name = $record->user?->name ?? 'Не указан';
                        $phone = $record->user?->phone ?? '';
                        
                        if ($phone) {
                            return $name . "<br>" . $phone;
                        }
                        return $name;
                    })
                    ->html()
                    ->wrap()
                    ->lineClamp(2)
                    ->sortable(['user.name'])
                    ->searchable(['user.name', 'user.phone'])
                    ->icon('heroicon-o-user')
                    // ->copyable()
                    ->copyMessage('Контакты менеджера скопированы!')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                TextColumn::make('customer_name')
                    ->label('Клиент')
                    ->formatStateUsing(function ($record) {
                        $name = $record->customer_name ?? 'Не указан';
                        $phone = $record->customer_phone ?? '';
                        
                        if ($phone) {
                            return $name . "<br>" . $phone;
                        }
                        return $name;
                    })
                    ->html()
                    ->lineClamp(2)
                    ->sortable()
                    ->wrap()
                    ->searchable(['customer_name', 'customer_phone'])
                    ->icon('heroicon-o-user-circle')
                    // ->copyable()
                    ->copyMessage('Контакты клиента скопированы!')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                TextColumn::make('ral_code')
                    ->label('RAL')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                TextColumn::make('total_price')
                    ->label('Сумма')
                    ->money('RUB')
                    ->sortable()
                    ->weight('semibold')
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn ($state) => $state?->format('d.m.Y H:i:s'))
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                self::getStatusColumn($hasEditAccess),
                
                TextColumn::make('invoice_id')
                    ->label('ID счета')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                TextColumn::make('invoice_status')
                    ->label('Статус счета')
                    ->badge()
                    ->color(fn (?string $state): string => 
                        self::INVOICE_STATUS_COLORS[$state] ?? 'gray'
                    )
                    ->formatStateUsing(fn (?string $state): string => 
                        self::INVOICE_STATUSES[$state] ?? ($state ?? '—')
                    )
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус заказа')
                    ->multiple()
                    ->options(self::ORDER_STATUSES)
                    ->indicator('Статус'),
                    
                Tables\Filters\SelectFilter::make('invoice_status')
                    ->label('Статус счета')
                    ->multiple()
                    ->options(self::INVOICE_STATUSES)
                    ->indicator('Счет'),
                    
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Создан с'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Создан до')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicator('Период'),
                    
                Tables\Filters\TernaryFilter::make('has_invoice')
                    ->label('Наличие счета')
                    ->nullable()
                    ->trueLabel('Со счетом')
                    ->falseLabel('Без счета')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('invoice_id'),
                        false: fn (Builder $query) => $query->whereNull('invoice_id'),
                        blank: fn (Builder $query) => $query,
                    )
                    ->indicator('Счет'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->visible(fn () => $hasEditAccess),
                    
                    Action::make('sketcher')
                        ->label('Чертеж')
                        ->url(fn (Order $record) => route('app.sketcher', $record->id))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-pencil-square'),
                        
                    Action::make('list_pdf')
                        ->label('Спецификация')
                        ->url(fn (Order $record) => route('orders.list_pdf', $record->id))
                        ->openUrlInNewTab()
                        ->icon('heroicon-o-document-arrow-down'),
                        // ->color('primary'),
                        
                    Action::make('create_contract')
                        ->label('Создать договор')
                        ->url(fn (Order $record) => route('filament.admin.resources.contracts.create', [
                            'order_id' => $record->id, 
                            'order_price' => $record->total_price
                        ]))
                        ->icon('heroicon-o-document-plus')
                        // ->color('success')
                        ->visible(fn (Order $record) => $record->contracts()->count() === 0),
                        
                    Action::make('view_contract')
                        ->label('Просмотр договора')
                        ->url(fn (Order $record) => route('filament.admin.resources.contracts.edit', [
                            'record' => $record->contracts()->first()->id
                        ]))
                        ->icon('heroicon-o-document-text')
                        // ->color('info')
                        ->visible(fn (Order $record) => $record->contracts()->count() > 0),
                        
                    ...self::getBillActions(),
                    
                    ActionsDeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Удалить заказ?')
                        ->modalDescription('Это действие нельзя отменить.')
                        ->modalSubmitActionLabel('Да, удалить')
                        ->visible(fn () => $user && $user->hasRole(self::ROLES['SUPER_ADMIN'])),
                ])
                ->icon('heroicon-o-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button()
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => $user && $user->hasRole(self::ROLES['SUPER_ADMIN'])),
                ])
                ->label('Массовые действия'),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->visible(fn () => $hasEditAccess),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25);
    }

    protected static function getStatusColumn(bool $hasEditAccess): TextColumn|SelectColumn
    {
        if ($hasEditAccess) {
            return SelectColumn::make('status')
                ->label('Статус')
                ->options(self::ORDER_STATUSES)
                ->selectablePlaceholder(false)
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: false);
        }

        return TextColumn::make('status')
            ->label('Статус')
            ->badge()
            ->color(fn (?string $state): string => 
                self::ORDER_STATUS_COLORS[$state] ?? 'gray'
            )
            ->formatStateUsing(fn (?string $state): string => 
                self::ORDER_STATUSES[$state] ?? ($state ?? '—')
            )
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: false);
    }

    protected static function getBillActions(): array
    {
        return [
            Action::make('create_bill')
                ->label('Создать счет')
                ->action(function (Order $record) {
                    try {
                        $tochkaService = new TochkaBankService();
                        $tochkaService->createBill($record);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Успешно!')
                            ->body('Счет создан успешно')
                            ->success()
                            ->duration(5000)
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Ошибка создания счета')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading('Создать счет для оплаты?')
                ->modalDescription('После создания счет будет отправлен клиенту.')
                ->modalSubmitActionLabel('Создать счет')
                ->icon('heroicon-o-plus-circle')
                // ->color('success')
                ->visible(fn (Order $record) => empty($record->invoice_id)),
                
            Action::make('check_payment_status')
                ->label('Проверить оплату')
                ->action(function (Order $record) {
                    try {
                        $tochkaService = new TochkaBankService();
                        $response = $tochkaService->getBillPaymentStatus($record);
                        
                        $status = $response['Data']['paymentStatus'] ?? 'unknown';
                        $statusText = self::INVOICE_STATUSES[$status] ?? 'неизвестен';
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Статус оплаты обновлен')
                            ->body("Текущий статус: {$statusText}")
                            ->info()
                            ->duration(5000)
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Ошибка проверки статуса')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                })
                ->icon('heroicon-o-credit-card')
                // ->color('info')
                ->visible(fn (Order $record) => !empty($record->invoice_id)),
                
            Action::make('invoice_pdf')
                ->label('Скачать счет PDF')
                ->url(fn (Order $record) => route('orders.download_bill', ['order' => $record->id]))
                ->openUrlInNewTab()
                ->icon('heroicon-o-document-arrow-down')
                // ->color('primary')
                ->visible(fn (Order $record) => !empty($record->invoice_id)),
                
            Action::make('send_bill_email')
                ->label('Отправить на email')
                ->form([
                    Forms\Components\TextInput::make('email')
                        ->label('Email получателя')
                        ->email()
                        ->required()
                        ->default(fn (Order $record) => $record->customer_email)
                        ->placeholder('example@mail.com')
                ])
                ->action(function (Order $record, array $data) {
                    try {
                        $tochkaService = new TochkaBankService();
                        $tochkaService->sendBillToEmail($record, $data['email']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Email отправлен!')
                            ->body("Счет отправлен на {$data['email']}")
                            ->success()
                            ->duration(5000)
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Ошибка отправки')
                            ->body($e->getMessage())
                            ->danger()
                            ->duration(10000)
                            ->send();
                    }
                })
                ->modalHeading('Отправить счет на email')
                ->modalSubmitActionLabel('Отправить')
                ->icon('heroicon-o-envelope')
                // ->color('warning')
                ->visible(fn (Order $record) => !empty($record->invoice_id)),
        ];
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
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        if (Auth::user()->hasRole(self::ROLES['SUPER_ADMIN'])) {
            return Cache::remember(
                'orders_badge_count', 
                60, // 1 minute
                fn() => static::getModel()::where('status', 'created')->count()
            );
        } else {
            return Cache::remember(
                'orders_badge_count', 
                60, // 1 minute
                fn() => static::getModel()::where('status', 'created')->where('user_id', Auth::user()->id)->count()
            );
        }
    }
    
    // public static function getNavigationBadgeColor(): ?string
    // {
    //     $count = (int) static::getNavigationBadge();
        
    //     return match (true) {
    //         $count === 0 => 'success',
    //         $count <= 5 => 'warning', 
    //         default => 'danger'
    //     };
    // }
}
