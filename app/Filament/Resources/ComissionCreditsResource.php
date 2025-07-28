<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComissionCreditsResource\Pages;
use App\Filament\Resources\ComissionCreditsResource\RelationManagers;
use App\Models\ComissionCredits;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ComissionCreditsResource extends Resource
{
    protected static ?string $model = ComissionCredits::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('receipt')
                    ->label('Чек')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Тип')
                    ->options([
                        'income' => 'Доход',
                        'expense' => 'Расход',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Сумма')
                    ->required(),
                Forms\Components\Select::make('parent_id')
                    ->label('Кто получает')
                    ->searchable()
                    ->options(User::all()->pluck('name', 'id'))
                    ->required(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user_id')
                    ->label('Инициатор')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
                    ->label('Заказ')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('parent_id')
                    ->label('Получатель комиссии')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Сумма')
                    ->searchable()
                    ->color(fn ($record) => $record->type === 'income' ? 'success' : 'danger')
                    ->money('RUB')
                    ->sortable(),
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
            'index' => Pages\ListComissionCredits::route('/'),
            'create' => Pages\CreateComissionCredits::route('/create'),
            'edit' => Pages\EditComissionCredits::route('/{record}/edit'),
        ];
    }
}
