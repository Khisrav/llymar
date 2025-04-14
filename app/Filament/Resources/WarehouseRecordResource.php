<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WarehouseRecordResource\Pages;
use App\Filament\Resources\WarehouseRecordResource\RelationManagers;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\WarehouseRecord;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseRecordResource extends Resource
{
    protected static ?string $model = WarehouseRecord::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Записи склада';
    protected static ?string $navigationGroup = 'Склад';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('item_id')
                    ->label('Деталь')
                    ->options(Item::all()->mapWithKeys(function ($vc) {
                        return [$vc->id => $vc->name];
                    }))
                    ->searchable()
                    // ->getSearchResultsUsing(fn (string $search) => Item::where('name', 'like', "%{$search}%")->limit(10)->pluck('name', 'id')->toArray())
                    ->required(),
                TextInput::make('quantity')
                    ->label('Кол-во')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                ImageColumn::make('item_img')
                    ->label('Картинка')
                    ->getStateUsing(fn (Model $record) => Item::find($record->item_id)->img)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('item_name')
                    ->label('Наименование')
                    ->searchable()
                    ->wrap()
                    ->getStateUsing(fn (Model $record) => Item::find($record->item_id)->name)
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('order_id')
                    ->label('ID заказа')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->sortable()
                    ->badge()
                    ->suffix(fn (Model $record) => ' ' . Item::find($record->item_id)->unit)
                    ->color(fn (Model $record) => $record->quantity == 0 ? 'gray' : ($record->quantity > 0 ? 'green' : 'red'))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label('Создан')
                    ->sortable()
                    ->since()
                    ->dateTimeTooltip()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('item_id')
                    ->label('Деталь')
                    ->native(false)
                    ->searchable()
                    ->optionsLimit(20)
                    ->options(Item::all()->pluck('name', 'id')->toArray()),
                SelectFilter::make('warehouse_id')
                    ->label('Склад')
                    ->native(false)
                    ->searchable()
                    ->optionsLimit(10)
                    ->options(Warehouse::all()->pluck('name', 'id')->toArray()),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListWarehouseRecords::route('/'),
            'create' => Pages\CreateWarehouseRecord::route('/create'),
            'edit' => Pages\EditWarehouseRecord::route('/{record}/edit'),
        ];
    }
}
