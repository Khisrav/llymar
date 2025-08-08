<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PortfolioResource\Pages;
use App\Models\Portfolio;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PortfolioResource extends Resource
{
    protected static ?string $model = Portfolio::class;
    protected static ?string $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Портфолио';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?string $pluralLabel = 'Портфолио';
    protected static ?string $modelLabel = 'Портфолио';
    protected static ?string $label = 'Портфолио';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // === Основная информация ===
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Название проекта')
                            ->placeholder('Остекление балкона')
                            ->required()
                            ->columnSpanFull(),
    
                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->placeholder('Кратко опишите проект...')
                            ->rows(4)
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'bg-white shadow-sm p-4 rounded-xl'])
                    ->heading('Основная информация'),
    
                // === Детали проекта ===
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('area')
                            ->numeric()
                            ->required()
                            ->suffix(' м²')
                            ->prefixIcon('heroicon-o-square-2-stack')
                            ->label('Площадь'),
    
                        Forms\Components\TextInput::make('color')
                            ->required()
                            ->prefixIcon('heroicon-o-swatch')
                            ->label('Цвет'),
    
                        Forms\Components\TextInput::make('glass')
                            ->required()
                            ->prefixIcon('heroicon-o-view-columns')
                            ->label('Тип стекла'),
    
                        Forms\Components\TextInput::make('location')
                            ->required()
                            ->prefixIcon('heroicon-o-map-pin')
                            ->label('Локация'),
    
                        Forms\Components\TextInput::make('year')
                            ->numeric()
                            ->required()
                            ->minValue(1900)
                            ->maxValue(date('Y'))
                            ->prefixIcon('heroicon-o-calendar')
                            ->label('Год'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->heading('Детали проекта')
                    ->extraAttributes(['class' => 'bg-white shadow-sm p-4 rounded-xl']),
    
                // === Изображения ===
                Section::make()
                    ->schema([
                        Forms\Components\FileUpload::make('images')
                            ->label('Фотографии проекта')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            // ->enableReordering()
                            ->directory('portfolio')
                            ->hint('Загрузите несколько изображений для карусели')
                            ->imagePreviewHeight('150')
                            ->downloadable(),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->heading('Галерея изображений')
                    ->extraAttributes(['class' => 'bg-white shadow-sm p-4 rounded-xl']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Превью')
                    ->square()
                    ->size(60),
    
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
    
                Tables\Columns\TextColumn::make('location')
                    ->label('Локация')
                    ->limit(20)
                    ->sortable(),
    
                Tables\Columns\TextColumn::make('area')
                    ->label('Площадь')
                    ->badge()
                    ->suffix(' м²')
                    ->color('gray')
                    ->sortable(),
    
                Tables\Columns\TextColumn::make('year')
                    ->label('Год')
                    ->sortable(),
    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->date('d.m.Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPortfolios::route('/'),
            'create' => Pages\CreatePortfolio::route('/create'),
            'edit' => Pages\EditPortfolio::route('/{record}/edit'),
        ];
    }
}
