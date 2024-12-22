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

class ItemResource extends Resource
{
    protected static ?string $model = Item::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                        Forms\Components\TextInput::make('vendor_code')
                            ->label('Артикул'),
                        Forms\Components\TextInput::make('name')
                            ->label('Наименование')
                            ->required(),
                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Закупка, ₽')
                            ->type('number')
                            ->prefix('₽')
                            ->required(),
                        Forms\Components\TextInput::make('retail_price')
                            ->label('Розница, ₽')
                            ->type('number')
                            ->prefix('₽')
                            ->required(),
                        Forms\Components\Select::make('unit')
                            ->label('Единица измерения')
                            ->native(false)
                            ->options(['шт.' => 'шт.', 'кг' => 'кг', 'м' => 'м', 'м.п.' => 'м.п.', 'м.кв.' => 'м.кв.', 'мм' => 'мм', 'створ.' => 'створ.'])
                            ->required(),
                        Forms\Components\Select::make('category_id')                    
                            ->label('Категория')
                            ->native(false)
                            ->options(Category::all()->pluck('name', 'id'))
                            ->required(),                   
                        Forms\Components\FileUpload::make('img')
                            ->label('Картинка')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->directory('items'),
                        // Forms\Components\Toggle::make('is_for_llymar')
                        //     ->label('Для LLYMAR')
                        //     // ->default(false)
                        //     ->helperText('Включение товара в калькуляторе LLYMAR (блок с доп. деталями)'),
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
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('vendor_code')
                    ->label('Арт.')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\ImageColumn::make('img')
                    ->label('Картинка')
                    ->width(100)
                    ->height('auto')
                    // ->url(fn ($record) => $record->img)
                    ->disk('public')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('purchase_price')
                    ->label('Закупка, ₽')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('retail_price')
                    ->label('Розница, ₽')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
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
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Категория')
                    ->options(Category::all()->pluck('name', 'id')),
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
            'index' => Pages\ListItems::route('/'),
            'create' => Pages\CreateItem::route('/create'),
            'edit' => Pages\EditItem::route('/{record}/edit'),
        ];
    }
}
