<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LandingPageOptionResource\Pages;
use App\Models\LandingPageOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LandingPageOptionResource extends Resource
{
    protected static ?string $model = LandingPageOption::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'Настройки главной';
    protected static ?string $modelLabel = 'Настройка';
    protected static ?string $pluralModelLabel = 'Настройки главной';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLandingPageOptions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
