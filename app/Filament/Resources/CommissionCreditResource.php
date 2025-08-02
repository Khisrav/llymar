<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionCreditResource\Pages;
use App\Models\CommissionCredit;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class CommissionCreditResource extends Resource
{
    protected static ?string $model = CommissionCredit::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Комиссионные операции';
    protected static ?string $label = 'Комиссионная операция';
    protected static ?string $pluralLabel = 'Комиссионные операции';
    protected static ?string $pluralModelLabel = 'Комиссионные операции';
    protected static ?string $modelLabel = 'Комиссионная операция';
    //protected static ?string $navigationGroup = 'Финансы';

    /**
     * Get the current authenticated user with proper typing
     */
    protected static function getCurrentUser(): ?User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Check if current user is Super Admin
     */
    protected static function isSuperAdmin(): bool
    {
        $user = static::getCurrentUser();
        return $user && $user->hasRole('Super-Admin');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = static::getCurrentUser();
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }
        
        if ($user->hasRole('Super-Admin')) {
            return parent::getEloquentQuery();
        }
        
        // Non-admin users can only see their own records
        return parent::getEloquentQuery()->where(function ($query) use ($user) {
            $query->where('parent_id', $user->id)
                  ->orWhere('user_id', $user->id);
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Main Information Section
                Forms\Components\Section::make('Основная информация')
                    ->description('Информация о выплате комиссионных средств')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                // Fixed to write-off only
                                Forms\Components\Hidden::make('type')
                                    ->default('write-off'),
                                
                                Forms\Components\TextInput::make('amount')
                                    ->label('Сумма выплаты')
                                    ->helperText('Укажите сумму в рублях')
                                    ->numeric()
                                    ->minValue(0.01)
                                    ->step(0.01)
                                    ->prefix('₽')
                                    ->required()
                                    ->placeholder('0.00')
                                    ->columnSpan(2),

                                Forms\Components\Select::make('parent_id')
                                    ->label('Получатель комиссии')
                                    ->helperText('Пользователь, который получает выплату')
                                    ->searchable()
                                    ->preload()
                                    ->options(function () {
                                        return Cache::remember('commission_recipients', 300, function () {
                                            if (static::isSuperAdmin()) {
                                                return User::whereHas('roles', function ($query) {
                                                    $query->whereIn('name', ['ROP']);
                                                })->orderBy('name')->pluck('name', 'id');
                                            } else {
                                                // Non-admin users can only see their hierarchy
                                                $currentUser = static::getCurrentUser();
                                                if (!$currentUser) return [];
                                                
                                                return User::where('parent_id', $currentUser->id)
                                                    ->orWhere('id', $currentUser->id)
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id');
                                            }
                                        });
                                    })
                                    ->required()
                                    ->columnSpan(2),
                            ]),
                    ])
                    ->collapsible(),

                // Receipt Section
                Forms\Components\Section::make('Документы')
                    ->description('Обязательный чек или документ подтверждающий выплату')
                    ->icon('heroicon-o-document-arrow-up')
                    ->schema([
                        Forms\Components\FileUpload::make('receipt')
                            ->label('Чек или документ')
                            ->helperText('Загрузите чек, документ или скриншот подтверждающий выплату')
                            ->acceptedFileTypes([
                                'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
                                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                            ])
                            ->directory('commission-receipts')
                            ->visibility('private')
                            ->downloadable()
                            ->previewable()
                            ->required()
                            ->maxSize(10240) // 10MB
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                // Optional Reference Section
                Forms\Components\Section::make('Дополнительная информация')
                    ->description('Опциональная привязка к заказу или пользователю')
                    ->icon('heroicon-o-link')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Инициатор заказа')
                                    ->helperText('Пользователь, с чьего заказа была получена комиссия')
                                    ->searchable()
                                    ->options(function () {
                                        return User::whereHas('roles', function ($query) {
                                            $query->whereIn('name', ['Dealer']);
                                        })->orderBy('name')->pluck('name', 'id');
                                    })
                                    ->live()
                                    ->afterStateUpdated(fn (callable $set) => $set('order_id', null))
                                    ->nullable(),

                                Forms\Components\Select::make('order_id')
                                    ->label('Связанный заказ')
                                    ->helperText('Заказы выбранного инициатора без существующих комиссий')
                                    ->searchable()
                                    ->disabled(fn (Forms\Get $get) => !$get('user_id'))
                                    ->options(function (Forms\Get $get) {
                                        $userId = $get('user_id');
                                        if (!$userId) {
                                            return [];
                                        }

                                        // Show orders that belong to the selected user and don't already have commission records
                                        return Order::where('user_id', $userId)
                                            ->whereNotIn('id', 
                                                CommissionCredit::whereNotNull('order_id')->pluck('order_id')
                                            )
                                            ->orderBy('order_number', 'desc')
                                            ->pluck('order_number', 'id');
                                    })
                                    ->nullable(),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->url(fn (Model $record) => $record->order ? "#" : null)
                    ->color('primary')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('recipient.name')
                    ->label('Получатель')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Model $record) => $record->recipient?->email)
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Инициатор')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->description(fn (Model $record) => $record->user?->company)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->since()
                    ->tooltip(fn (Model $record) => $record->created_at->format('d.m.Y H:i')),
                
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип операции')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'accrual' => 'success',
                        'write-off' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'accrual' => 'Начисление',
                        'write-off' => 'Выплата',
                        default => $state,
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->money('RUB', locale: 'ru')
                    ->sortable()
                    ->weight('medium'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип операции')
                    ->options([
                        'accrual' => 'Начисление',
                        'write-off' => 'Выплата',
                    ])
                    ->native(false),

                Tables\Filters\SelectFilter::make('recipient')
                    ->label('Получатель')
                    ->relationship('recipient', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\SelectFilter::make('user')
                    ->label('Инициатор')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                Tables\Filters\Filter::make('amount_range')
                    ->label('Диапазон сумм')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('amount_from')
                                    ->label('От')
                                    ->numeric()
                                    ->prefix('₽'),
                                Forms\Components\TextInput::make('amount_to')
                                    ->label('До')
                                    ->numeric()
                                    ->prefix('₽'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['amount_from'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '>=', $amount),
                            )
                            ->when(
                                $data['amount_to'],
                                fn (Builder $query, $amount): Builder => $query->where('amount', '<=', $amount),
                            );
                    }),

                Tables\Filters\Filter::make('with_receipt')
                    ->label('С чеком')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('receipt'))
                    ->toggle(),

                Tables\Filters\Filter::make('date_range')
                    ->label('Период')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('С даты'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('По дату'),
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
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Просмотр')
                        ->modalWidth('2xl'),

                    Tables\Actions\EditAction::make()
                        ->label('Редактировать')
                        ->visible(fn (CommissionCredit $record) => 
                            $record->type === 'write-off' && static::isSuperAdmin()
                        )
                        ->modalWidth('2xl'),

                    Tables\Actions\DeleteAction::make()
                        ->label('Удалить')
                        ->visible(fn (CommissionCredit $record) => 
                            $record->type === 'write-off' && static::isSuperAdmin()
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Удалить запись о выплате?')
                        ->modalDescription('Это действие нельзя будет отменить.')
                        ->successNotificationTitle('Запись о выплате удалена'),
                ])
            ])
            ->headerActions([])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(static::isSuperAdmin())
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('Нет комиссионных операций')
            ->emptyStateDescription('Комиссионные операции будут отображаться здесь после их создания.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommissionCredits::route('/'),
            'create' => Pages\CreateCommissionCredit::route('/create'),
            'view' => Pages\ViewCommissionCredit::route('/{record}'),
            'edit' => Pages\EditCommissionCredit::route('/{record}/edit'),
        ];
    }
    
    public static function canAccess(): bool
    {
        $user = static::getCurrentUser();
        return $user && $user->hasRole(['Super-Admin', 'ROP']);
    }

    public static function getNavigationBadge(): ?string
    {
        $user = static::getCurrentUser();
        if (!$user) return null;
        
        return Cache::remember(
            "commission_credits_count_badge_{$user->id}", 
            300, 
            fn () => static::getEloquentQuery()->count()
        );
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['amount', 'recipient.name', 'user.name', 'order.order_number'];
    }
} 