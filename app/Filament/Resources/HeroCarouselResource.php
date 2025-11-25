<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroCarouselResource\Pages;
use App\Models\HeroCarousel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HeroCarouselResource extends Resource
{
    protected static ?string $model = HeroCarousel::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';
    
    protected static ?string $navigationLabel = 'Карусель героя';
    protected static ?string $modelLabel = 'Слайд';
    protected static ?string $pluralModelLabel = 'Карусель героя';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\FileUpload::make('background')
                            ->label('Фон (изображение)')
                            ->required()
                            ->directory('hero-carousels')
                            ->disk('public')
                            ->acceptedFileTypes(['image/*'])
                            ->optimize('webp')
                            ->resize(50)
                            ->helperText('Загрузите изображение для фона слайда')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('action_text')
                            ->label('Текст кнопки действия')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('order')
                            ->label('Порядок')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Используется для сортировки слайдов'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Активен')
                            ->default(true)
                            ->helperText('Только активные слайды отображаются на сайте'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('Порядок')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\ImageColumn::make('background')
                    ->label('Фон')
                    ->disk('public')
                    ->square(),

                Tables\Columns\TextColumn::make('action_text')
                    ->label('Текст кнопки')
                    ->limit(30)
                    ->default('—'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активен')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Активность')
                    ->placeholder('Все')
                    ->trueLabel('Только активные')
                    ->falseLabel('Только неактивные'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order');
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
            'index' => Pages\ListHeroCarousels::route('/'),
            'create' => Pages\CreateHeroCarousel::route('/create'),
            'edit' => Pages\EditHeroCarousel::route('/{record}/edit'),
        ];
    }
}
