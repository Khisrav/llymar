<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderJournalResource\Pages;
use App\Filament\Resources\OrderJournalResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class OrderJournalResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Журнал цеха';
    protected static ?string $pluralModelLabel = 'Журнал цеха';
    // protected static ?string $navigationGroup = 'Заказы';
    
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['orderItems.item', 'orderOpenings.doorHandle']);
            })
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->wrapHeader()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->wrapHeader()
                    ->dateTime('d M Y')
                    ->tooltip(fn ($state): string => $state->format('d.m.Y H:i'))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_address')
                    ->label('Адрес')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => 
                        self::ORDER_STATUSES[$state] ?? ($state ?? '—')
                    )
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->colors(self::ORDER_STATUS_COLORS),
                //which glass is in the order
                Tables\Columns\TextColumn::make('glass_name')
                    ->label('Стекло')
                    ->searchable()
                    ->size('xs')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                //door handles from order openings
                Tables\Columns\TextColumn::make('handles')
                    ->label('Ручки')
                    ->searchable()
                    ->size('xs')
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('raspil')
                    ->label('Распил')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('ral_code')
                    ->label('RAL')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->color('gray')
                    ->wrap(),
                Tables\Columns\TextColumn::make('priem_stekla')
                    ->label('Прием стекла')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('pokleyka')
                    ->label('Поклейка')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('upakovka')
                    ->label('Упаковка')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('montazh')
                    ->label('Мотнаж \ Отправка')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Общая \ Аванс \ Остаток')
                    ->wrapHeader()
                    ->money('RUB')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderJournals::route('/'),
            'create' => Pages\CreateOrderJournal::route('/create'),
            'edit' => Pages\EditOrderJournal::route('/{record}/edit'),
        ];
    }
}
