<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderItems';
    
    protected static ?string $title = 'Детали';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\Select::make('item_id')
                    ->required()
                    ->label('ID детали')
                    ->options(Item::all()->mapWithKeys(function ($vc) {
                        return [$vc->id => $vc->id . ' - ' . $vc->name];
                    })),
                Forms\Components\TextInput::make('quantity')
                    ->required()
                    ->label('Кол-во')
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_id')
            ->columns([
                // Add ImageColumn here
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image')
                    ->getStateUsing(function (Model $record) {
                        $item = Item::find($record->item_id);
                        return $item ? $item->img : null; 
                    })
                    ->size(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('item_id')
                    ->label('ID')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('item_name')
                    ->label('Деталь')
                    ->state(fn ($record) => Item::find($record->item_id)->name)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кол-во')
                    ->suffix(function (Model $record) {
                        return ' ' . Item::find($record->item_id)->unit;
                    })
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Итого')
                    ->state(function (Model $record) {
                        $item = Item::where('id', $record->item_id)->first();
                        
                        return number_format($record->quantity * $item->retail_price * (1 - ($item->discount || auth()->user()->discount) / 100), 0, '.', ' ') . ' ₽';
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
