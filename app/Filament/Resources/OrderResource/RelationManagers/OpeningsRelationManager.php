<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderOpening;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OpeningsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderOpenings';
    
    protected static ?string $title = 'Проемы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('order_id')
                    ->required()
                    ->label('ID заказа')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->required()
                    ->label('Тип проема')
                    ->native(false)
                    ->options([
                        'left' => 'Левый проем',
                        'right' => 'Правый проем',
                        'center' => 'Центральный проем',
                        'inner-left' => 'Входная группа левая',
                        'inner-right' => 'Входная группа правая',
                        'blind-glazing' => 'Глухое остекление',
                        'triangle' => 'Треугольник',
                    ]),
                Forms\Components\TextInput::make('doors')
                    ->required()
                    ->label('Кол-во створок')
                    ->maxLength(255),
                Forms\Components\TextInput::make('width')
                    ->required()
                    ->label('Ширина (мм)')
                    ->maxLength(255),
                Forms\Components\TextInput::make('height')
                    ->required()
                    ->label('Высота (мм)')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('order_id')
            ->columns([
                // Tables\Columns\TextColumn::make('order_id'),
                Tables\Columns\SelectColumn::make('type')
                    ->label('Тип проема')
                    ->searchable()
                    ->selectablePlaceholder(false)
                    // ->options(Opening::all()->pluck('name', 'type')),
                    ->options([
                        'left' => 'Левый проем',
                        'right' => 'Правый проем',
                        'center' => 'Центральный проем',
                        'inner-left' => 'Входная группа левая',
                        'inner-right' => 'Входная группа правая',
                        'blind-glazing' => 'Глухое остекление',
                        'triangle' => 'Треугольник',
                    ])
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('doors')
                    ->label('Створки')
                    ->sortable()
                    ->suffix(' ств.')
                    // ->type('number')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('height')
                    ->label('Высота')
                    ->sortable()
                    ->suffix('мм')
                    // ->type('number')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('width')
                    ->label('Ширина')
                    ->sortable()
                    ->suffix('мм')
                    // ->type('number')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('id')
                    ->label('Параметры')
                    ->wrap()
                    ->formatStateUsing(function (OrderOpening $record) {
                        return 'a: ' . $record->a . 'мм, b: ' . $record->b . 'мм, c: ' . $record->c . 'мм, d: ' . $record->d . 'мм, e: ' . $record->e . 'мм, f: ' . $record->f . 'мм, g: ' . $record->g . 'мм, i: ' . $record->i . 'мм';
                    }),
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
