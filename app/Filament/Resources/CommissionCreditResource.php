<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommissionCreditResource\Pages;
use App\Filament\Resources\CommissionCreditResource\RelationManagers;
use App\Models\CommissionCredit;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class CommissionCreditResource extends Resource
{
    protected static ?string $model = CommissionCredit::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Комиссионные';
    protected static ?string $label = 'Комиссионный';
    protected static ?string $pluralLabel = 'Комиссионные';
    protected static ?string $pluralModelLabel = 'Комиссионные';
    protected static ?string $modelLabel = 'Комиссионноe';
    // protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('receipt')
                    ->label('Чек')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->directory('receipts')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Тип')
                    ->options([
                        'accrual' => 'Начисление комиссии',
                        'write-off' => 'Списание (Выплата комиссии)',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Сумма')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Инициатор заказа')
                    ->searchable()
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('name', 'Dealer');
                    })->pluck('name', 'id'))
                    ->nullable(),
                Forms\Components\Select::make('order_id')
                    ->label('Заказ')
                    ->searchable()
                    ->options(
                        Order::whereNotIn('id', CommissionCredit::whereNotNull('order_id')->pluck('order_id'))
                            ->pluck('order_number', 'id')
                    )
                    ->nullable(),
                Forms\Components\Select::make('parent_id')
                    ->label('Получатель комиссии')
                    ->searchable()
                    ->options(User::whereHas('roles', function ($query) {
                        $query->where('name', 'ROP');
                    })->pluck('name', 'id'))
                    ->required(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Filter records to show only those related to ROP users
                $user = Auth::user();
                if ($user && !$user->hasRole('Super-Admin')) {
                    $query->where('parent_id', Auth::id())
                          ->orWhere('user_id', Auth::id());
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Инициатор')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('recipient.name')
                    ->label('Получатель комиссии')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->searchable()
                    ->badge()
                    ->color(fn ($record) => $record->type === 'accrual' ? 'success' : 'danger')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'accrual' => 'Начисление',
                        'write-off' => 'Списание',
                        default => $state,
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->searchable()
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'accrual' => 'Начисление',
                        'write-off' => 'Списание',
                    ]),
                Tables\Filters\SelectFilter::make('recipient')
                    ->label('Получатель')
                    ->relationship('recipient', 'name')
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (CommissionCredit $record) => $record->type === 'write-off' && Auth::user()->hasRole('Super-Admin')),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (CommissionCredit $record) => $record->type === 'write-off' && Auth::user()->hasRole('Super-Admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
        $user = Auth::user();
        return $user && $user->hasRole(['Super-Admin', 'ROP']);
    }
} 