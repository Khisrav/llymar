<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\CommissionCredit;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class ReceivedCommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'receivedCommissions';
    protected static ?string $title = 'Комиссионные операции';
    protected static ?string $modelLabel = 'Комиссионная операция';
    protected static ?string $pluralModelLabel = 'Комиссионные операции';
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    /**
     * Get the current authenticated user with proper typing
     */
    protected function getCurrentUser(): ?User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user;
    }

    /**
     * Check if current user is Super Admin
     */
    protected function isSuperAdmin(): bool
    {
        $user = $this->getCurrentUser();
        return $user && $user->hasRole('Super-Admin');
    }

    public function form(Form $form): Form
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
                                        if ($this->isSuperAdmin()) {
                                            return User::whereHas('roles', function ($query) {
                                                $query->whereIn('name', ['ROP']);
                                            })->orderBy('name')->pluck('name', 'id');
                                        } else {
                                            // Non-admin users can only see their hierarchy
                                            $currentUser = $this->getCurrentUser();
                                            if (!$currentUser) return [];
                                            
                                            return User::where('parent_id', $currentUser->id)
                                                ->orWhere('id', $currentUser->id)
                                                ->orderBy('name')
                                                ->pluck('name', 'id');
                                        }
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

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                /** @var \App\Models\User|null $user */
                $user = $this->getCurrentUser();
                
                if (!$user) {
                    return $query->whereRaw('1 = 0'); // Return no results if no user
                }
                
                if (!$user->hasRole('Super-Admin')) {
                    // Non-admin users can only see records where they are the recipient or initiator
                    $query->where(function ($q) use ($user) {
                        $q->where('parent_id', $user->id)
                          ->orWhere('user_id', $user->id);
                    });
                }
            })
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
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить выплату')
                    ->icon('heroicon-o-plus')
                    ->visible($this->isSuperAdmin())
                    ->successNotificationTitle('Выплата успешно добавлена')
                    ->modalHeading('Добавить выплату комиссии')
                    ->modalDescription('Создание записи о выплате комиссионных средств')
                    ->modalWidth('2xl'),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('Просмотр')
                        ->tooltip('Просмотр')
                        ->modalWidth('2xl'),
    
                    Tables\Actions\EditAction::make()
                        ->label('Редактировать')
                        ->tooltip('Редактировать')
                        ->visible(fn (CommissionCredit $record) => 
                            $record->type === 'write-off' && $this->isSuperAdmin()
                        )
                        ->modalWidth('2xl'),
    
                    Tables\Actions\DeleteAction::make()
                        ->label('Удалить')
                        ->tooltip('Удалить')
                        ->visible(fn (CommissionCredit $record) => 
                            $record->type === 'write-off' && $this->isSuperAdmin()
                        )
                        ->requiresConfirmation()
                        ->modalHeading('Удалить запись о выплате?')
                        ->modalDescription('Это действие нельзя будет отменить.')
                        ->successNotificationTitle('Запись о выплате удалена'),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($this->isSuperAdmin())
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50])
            ->emptyStateHeading('Нет комиссионных операций')
            ->emptyStateDescription('Комиссионные операции будут отображаться здесь после их создания.')
            ->emptyStateIcon('heroicon-o-banknotes');
    }

    /**
     * Get the total balance for the current user
     */
    public function getTotalBalance(): float
    {
        $ownerRecord = $this->getOwnerRecord();
        if (!$ownerRecord instanceof User) {
            return 0.0;
        }

        $accruals = CommissionCredit::where('parent_id', $ownerRecord->id)
            ->where('type', 'accrual')
            ->sum('amount');

        $writeOffs = CommissionCredit::where('parent_id', $ownerRecord->id)
            ->where('type', 'write-off')
            ->sum('amount');

        return $accruals - $writeOffs;
    }
}
