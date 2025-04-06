<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WarehouseRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'warehouseRecords';
    
    protected static ?string $title = 'Записи склада';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('item_id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Количество')
                    ->sortable()
                    ->suffix(fn (Model $record) => ' ' . Item::find($record->item_id)->unit)
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип записи')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state > 0 ? 'Приход' : 'Расход')
                    ->color(fn (string $state): string => $state > 0 ? 'success' : 'danger')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
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
}
