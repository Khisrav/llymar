<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Filament\Resources\UserResource\RelationManagers\Widgets\CommissionCreditsOverviewByUser as WidgetsCommissionCreditsOverviewByUser;
use App\Models\CommissionCredit;
use App\Models\Order;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\AccountWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class ReceivedCommissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'receivedCommissions';
    protected static ?string $title = 'Комиссионные';
    //protected static ?string $pluralTitle = 'Комиссионные';
    //protected static ?string $modelLabel = 'Комиссионные';
    //protected static ?string $pluralModelLabel = 'Комиссионные';
    
    public static function getWidgets(): array
    {
        return [
            AccountWidget::class,
            // WidgetsCommissionCreditsOverviewByUser::class,
        ];
    }
    
    public function getHeaderWidgets(): array
    {
        return [
            AccountWidget::class,
            // WidgetsCommissionCreditsOverviewByUser::class,
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('receipt')
                    ->label('Чек')
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->directory('receipts'),
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

    public function table(Table $table): Table
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
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->visible(Auth::user()->hasRole('Super-Admin')),
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
}
