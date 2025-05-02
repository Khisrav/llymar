<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Item;
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
        return $form->schema([
            // Basic opening fields
            Forms\Components\Fieldset::make('Основные данные')
                ->schema([
                    Forms\Components\TextInput::make('order_id')
                        ->label('ID заказа')
                        ->disabled()
                        ->maxLength(255),
    
                    Forms\Components\Select::make('type')
                        ->label('Тип проема')
                        ->native(false)
                        ->options([
                            'left'           => 'Левый проем',
                            'right'          => 'Правый проем',
                            'center'         => 'Центральный проем',
                            'inner-left'     => 'Входная группа левая',
                            'inner-right'    => 'Входная группа правая',
                            'blind-glazing'  => 'Глухое остекление',
                            'triangle'       => 'Треугольник',
                        ])
                        ->required(),
    
                    Forms\Components\TextInput::make('doors')
                        ->label('Кол-во створок')
                        ->required()
                        ->maxLength(255),
    
                    Forms\Components\TextInput::make('width')
                        ->label('Ширина (мм)')
                        ->required()
                        ->maxLength(255),
    
                    Forms\Components\TextInput::make('height')
                        ->label('Высота (мм)')
                        ->required()
                        ->maxLength(255),
                ]),
    
            // Opening parameter fields (a, b, c, d, e, f, g, i, door_handle_item_id)
            Forms\Components\Fieldset::make('Параметры проема')
                ->schema([
                    Forms\Components\Grid::make(4) // 4 columns
                        ->schema([
                            Forms\Components\TextInput::make('a')
                                ->label('a (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('b')
                                ->label('b (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('c')
                                ->label('c (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('d')
                                ->label('d (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('e')
                                ->label('e (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('f')
                                ->label('f (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('g')
                                ->label('g (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\TextInput::make('i')
                                ->label('i (мм)')
                                ->numeric()
                                ->minValue(0)
                                ->default(0),
    
                            Forms\Components\Select::make('door_handle_item_id')
                                ->label('Ручка')
                                ->selectablePlaceholder(false)
                                //all items with category id 29
                                ->options(Item::where('category_id', 29)->pluck('name', 'id'))
                        ]),
                ]),
        ]);
    }


    public function table(Table $table): Table
    {
        $user = auth()->user();
        if ($user->hasRole('Super-Admin') || $user->hasRole('Operator')) {
            $OpeningTypeColumn = Tables\Columns\SelectColumn::make('type')
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
                ->toggleable(isToggledHiddenByDefault: false);
        } else {
            $OpeningTypeColumn = Tables\Columns\TextColumn::make('type')
                ->label('Тип проема')
                ->searchable()
                ->sortable()
                ->formatStateUsing(function (OrderOpening $record) {
                    $types = [
                        'left' => 'Левый проем',
                        'right' => 'Правый проем',
                        'center' => 'Центральный проем',
                        'inner-left' => 'Входная группа левая',
                        'inner-right' => 'Входная группа правая',
                        'blind-glazing' => 'Глухое остекление',
                        'triangle' => 'Треугольник',
                    ];
                    return $types[$record->type];
                })
                ->toggleable(isToggledHiddenByDefault: false);
        }
        return $table
            ->recordTitleAttribute('order_id')
            ->columns([
                // Tables\Columns\TextColumn::make('order_id'),
                $OpeningTypeColumn,
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
                Tables\Columns\ImageColumn::make('door_handle_image')
                    ->label('Фото ручки')
                    ->width(64)
                    ->height('auto')
                    //get image url from item by door_handle_item_id
                    ->getStateUsing(function (OrderOpening $record) {
                        $item = Item::find($record->door_handle_item_id);
                        return $item ? $item->img : null;
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('door_handle_item_id')
                    ->label('Ручка')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->formatStateUsing(function (OrderOpening $record) {
                        return Item::find($record->door_handle_item_id)->name;
                    })
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
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
