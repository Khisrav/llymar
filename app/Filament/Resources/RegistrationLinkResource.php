<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistrationLinkResource\Pages;
use App\Models\RegistrationLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RegistrationLinkResource extends Resource
{
    protected static ?string $model = RegistrationLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    
    protected static ?string $navigationLabel = 'Ссылки регистрации';
    protected static ?string $modelLabel = 'Ссылка регистрации';
    protected static ?string $pluralModelLabel = 'Ссылки регистрации';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Параметры регистрации')
                    ->description('Настройки для нового дилера')
                    ->schema([
                        Forms\Components\TextInput::make('reward_fee')
                            ->label('Комиссия (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->default(0)
                            ->required()
                            ->helperText('Комиссионный процент для дилера'),

                        Forms\Components\Toggle::make('can_access_dxf')
                            ->label('Доступ к DXF')
                            ->default(false)
                            ->helperText('Разрешить доступ к экспорту DXF файлов'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Создал')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->creator->email),

                Tables\Columns\TextColumn::make('reward_fee')
                    ->label('Комиссия')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                Tables\Columns\IconColumn::make('can_access_dxf')
                    ->label('DXF')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->is_used) {
                            return 'Использована';
                        }
                        if ($record->expires_at->isPast()) {
                            return 'Истекла';
                        }
                        return 'Активна';
                    })
                    ->color(fn ($record) => match (true) {
                        $record->is_used => 'success',
                        $record->expires_at->isPast() => 'danger',
                        default => 'warning',
                    }),

                Tables\Columns\TextColumn::make('registeredUser.name')
                    ->label('Зарегистрированный пользователь')
                    ->searchable()
                    ->placeholder('—')
                    ->description(fn ($record) => $record->registered_user_id ? $record->registeredUser->email : null),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Истекает')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->expires_at->diffForHumans()),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создана')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'active' => 'Активные',
                        'used' => 'Использованные',
                        'expired' => 'Истекшие',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where('is_used', false)->where('expires_at', '>', now()),
                            'used' => $query->where('is_used', true),
                            'expired' => $query->where('is_used', false)->where('expires_at', '<=', now()),
                            default => $query,
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('copyLink')
                    ->label('Копировать ссылку')
                    ->icon('heroicon-o-clipboard-document')
                    ->color('info')
                    ->action(function ($record) {
                        Notification::make()
                            ->title('Ссылка скопирована')
                            ->success()
                            ->body('Ссылка скопирована в буфер обмена')
                            ->send();
                    })
                    ->extraAttributes(fn ($record) => [
                        'x-data' => '{}',
                        'x-on:click' => 'navigator.clipboard.writeText("' . $record->url . '")',
                    ])
                    ->visible(fn ($record) => $record->isValid()),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Нет ссылок регистрации')
            ->emptyStateDescription('Создайте новую ссылку для регистрации дилеров')
            ->emptyStateIcon('heroicon-o-link');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        // Super-Admin sees all links
        if ($user->hasRole('Super-Admin')) {
            return parent::getEloquentQuery();
        }

        // Operator, ROP, and Dealer see only their created links
        return parent::getEloquentQuery()->where('created_by', $user->id);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageRegistrationLinks::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        return $user->hasAnyRole(['Super-Admin', 'Operator', 'ROP', 'Dealer']);
    }
}
