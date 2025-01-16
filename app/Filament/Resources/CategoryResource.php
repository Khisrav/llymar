<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    protected static ?string $navigationLabel = 'Категории';
    protected ?string $title = 'Категории';
    protected ?string $heading = 'Категории';
    protected ?string $subheading = 'Категории';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Информация о категории')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        TextInput::make('name')
                            ->label('Название')
                            ->required(),
                    ]),
                Section::make('Коэффциенты уменьшения')
                    ->collapsible()
                    ->schema([
                        Repeater::make('reduction_factors')
                            ->label('')
                            ->schema([
                                TextInput::make('key')
                                    ->label('Название (KU1, KU2, ...)')
                                    ->regex('/^KU\d+$/')
                                    ->required(),
                                TextInput::make('value')
                                    ->label('Значение КУ')
                                    ->numeric()
                                    ->default(1)
                                    ->required(),
                            ])
                            ->grid(3)
                            ->addActionLabel('Добавить КУ')
                            ->collapsible()
                            ->defaultItems(0),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
