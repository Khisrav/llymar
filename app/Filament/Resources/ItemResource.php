<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Item;
use Filament\Tables;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filters\TrashedFilter;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ItemResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ItemResource\RelationManagers;
use App\Filament\Resources\ItemResource\RelationManagers\ItemPropertiesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\WarehouseRecordsRelationManager;
use Illuminate\Database\Eloquent\Model;

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Склад';

    // protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Товары';
    protected ?string $title = 'Товары';
    protected ?string $heading = 'Товары';
    protected ?string $subheading = 'Товары';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о товаре')
                    ->columns([
                        'sm' => 2,
                        'md' => 3,
                        'lg' => 3
                    ])
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Наименование')
                            ->columnSpanFull()
                            ->required(),
                        Forms\Components\TextInput::make('vendor_code')
                            ->placeholder('Напр. L123')
                            ->label('Артикул'),
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Закупка, ₽')
                            ->numeric()
                            ->inputMode('decimal')
                            ->prefix('₽')
                            ->required(),
                        Forms\Components\Select::make('unit')
                            ->label('Единица измерения')
                            ->native(false)
                            ->options(['шт.' => 'шт.', 'кг' => 'кг', 'м' => 'м', 'м.п.' => 'м.п.', 'м²' => 'м²', 'мм' => 'мм', 'створ.' => 'створ.'])
                            ->required(),
                        Forms\Components\Select::make('category_id')                    
                            ->label('Категория')
                            ->native(false)
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required(),                   
                        Forms\Components\FileUpload::make('img')
                            ->label('Основная картинка')
                            ->image()
                            ->required()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->default('no-image.jpg')
                            ->directory('items'),
                        Forms\Components\TextInput::make('weight')
                            ->label('Вес, кг')
                            ->default(0)
                            ->type('integer')
                            ->minValue(0),
                        Forms\Components\TextInput::make('abbreviation')
                            ->label('Аббр.')
                            ->placeholder('Напр. М1/10')
                            ->helperText('Аббревиатура товара, используется в заказах'),
                        Forms\Components\FileUpload::make('images')
                            ->label('Дополнительные изображения')
                            ->image()
                            ->multiple()
                            ->maxFiles(10)
                            ->imageEditor()
                            ->reorderable()
                            ->maxSize(1024)
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('items')
                            ->columnSpanFull()
                            ->helperText('Максимум 10 дополнительных изображений'),
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->columnSpanFull()
                            ->placeholder('Введите описание товара...'),
                        Forms\Components\Toggle::make('is_for_llymar')
                            ->label('Для LLYMAR')
                            // ->default(false)
                            ->helperText('Включение товара в калькуляторе LLYMAR (блок с доп. деталями)'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('vendor_code')
                    ->label('Арт.')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('img')
                    ->label('Изображ.')
                    ->width(86)
                    ->height('auto')
                    // ->url(fn ($record) => $record->img)
                    ->disk('public')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->wrap()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('abbreviation')
                    ->label('Аббр.')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                // Tables\Columns\TextInputColumn::make('retail_price')
                //     ->label('Розница, ₽')
                //     ->searchable()
                //     ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('unit')
                    ->label('Ед. изм.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('category_id')
                    ->label('Категория')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => Category::find($state)->name)
                    ->toggleable(isToggledHiddenByDefault: false),
                    // ->options(Category::all()->pluck('name', 'id')),
                Tables\Columns\ToggleColumn::make('is_for_llymar')
                    ->label('LLYMAR')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('quantity_in_warehouse')
                    ->label('В наличии')
                    ->badge()
                    ->color(fn (Model $record) => $record->quantity_in_warehouse == 0 ? 'gray' : ($record->quantity_in_warehouse > 0 ? 'green' : 'red'))
                    ->suffix(fn (Model $record) => ' ' . Item::find($record->id)->unit)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('purchase_price')
                    ->label('Закупка, ₽')
                    ->searchable()
                    ->type('number')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('pz')
                    ->label('ЗЦ')
                    ->searchable()
                    ->type('number')
                    ->step(0.01)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('p1')
                    ->label('Р1')
                    ->searchable()
                    ->type('number')
                    ->step(0.01)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextInputColumn::make('p2')
                    ->label('Р2')
                    ->searchable()
                    ->type('number')
                    ->step(0.01)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextInputColumn::make('p3')
                    ->label('Р3')
                    ->searchable()
                    ->type('number')
                    ->step(0.01)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextInputColumn::make('pr')
                    ->label('РЦ')
                    ->searchable()
                    ->type('number')
                    ->step(0.01)
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->multiple()
                    ->options(Category::all()->pluck('name', 'id')),
                
            ])
            ->actions([
                // Tables\Actions\DeleteAction::make(),
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
            WarehouseRecordsRelationManager::class,
            ItemPropertiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
