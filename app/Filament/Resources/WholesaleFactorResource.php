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
    protected static ?string $navigationLabel = 'Оптовые коэффициенты';
    protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Ключ')
                    ->startsWith('OPT')
                    ->required(),
                Forms\Components\TextInput::make('value')
                    ->label('Значение')
                    ->numeric()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextInputColumn::make('name')
                    ->label('Ключ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextInputColumn::make('value')
                    ->label('Значение')
                    ->searchable()
                    ->type('number')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
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
