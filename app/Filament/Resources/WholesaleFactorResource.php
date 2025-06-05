<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WholesaleFactorResource\Pages;
use App\Filament\Resources\WholesaleFactorResource\RelationManagers;
use App\Models\WholesaleFactor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WholesaleFactorResource extends Resource
{
    protected static ?string $model = WholesaleFactor::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Группы коэффициентов';
    protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('group_name')
                    ->label('Название группы коэффициентов')
                    ->startsWith('GROUP')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Ключ оптового коэффициента')
                    ->startsWith('OPT')
                    ->required(),
                Forms\Components\Select::make('reduction_factor_key')
                    ->label('Ключ коэффициента уменьшения')
                    ->native(false)
                    ->options(function () {
                        return array_combine(
                            array_map(fn($key) => 'KU' . $key, range(1, 10)),
                            array_map(fn($key) => 'KU' . $key, range(1, 10))
                        );
                    })
                    ->selectablePlaceholder(false)
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Значение оптового коэффициента')
                    ->numeric()
                    ->required(),
                Forms\Components\ColorPicker::make('color')
                    ->label('Цвет')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group_name')
                    ->label('Название группы')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Ключ ОПТ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('value')
                    ->label('Значение ОПТ')
                    ->searchable()
                    // ->type('number')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reduction_factor_key')
                    ->label('Ключ КУ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Цвет')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListWholesaleFactors::route('/'),
            'create' => Pages\CreateWholesaleFactor::route('/create'),
            'edit' => Pages\EditWholesaleFactor::route('/{record}/edit'),
        ];
    }
}
