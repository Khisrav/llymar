<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LlymarCalculatorItemResource\Pages;
use App\Filament\Resources\LlymarCalculatorItemResource\RelationManagers;
use App\Models\Item;
use App\Models\LlymarCalculatorItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LlymarCalculatorItemResource extends Resource
{
    protected static ?string $model = LlymarCalculatorItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Состав системы';
    protected static ?string $navigationGroup = 'Настройки';

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
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),
                Tables\Columns\TextColumn::make('item_id')
                    ->label('Наименование')
                    ->formatStateUsing(fn ($state) => Item::find($state)->name),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListLlymarCalculatorItems::route('/'),
            // 'create' => Pages\CreateLlymarCalculatorItem::route('/create'),
            // 'edit' => Pages\EditLlymarCalculatorItem::route('/{record}/edit'),
        ];
    }
}
