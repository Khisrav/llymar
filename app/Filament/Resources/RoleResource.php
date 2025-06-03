<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Spatie\Permission\Models\Role as ModelsRole;

class RoleResource extends Resource
{
    protected static ?string $model = ModelsRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Роли';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralModelLabel = 'Роли';
    protected static ?string $pluralLabel = 'Роли';
    protected static ?string $modelLabel = 'Роль';
    protected static ?string $label = 'Роль';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            // ->unique()
                            ->label('Название')
                            ->placeholder('Введите название роли')
                            ->helperText('Уникальное название роли для системы')
                            ->columnSpan(1)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('display_name')
                            ->label('Отображается как')
                            ->required()
                            ->placeholder('Введите отображаемое название')
                            ->helperText('Название роли, которое будет отображаться пользователям')
                            ->columnSpan(1)
                            ->maxLength(255),
                        Forms\Components\Select::make('permissions')
                            ->label('Разрешения')
                            ->native(false)
                            ->searchable()
                            ->columnSpan(2)
                            ->helperText('Выберите разрешения для этой роли')
                            ->multiple()
                            ->preload()
                            ->relationship('permissions', 'name', fn ($query) => $query->where('guard_name', 'web'))
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name ?: $record->name),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('display_name')
                    ->label('Отображается как')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Доступов')
                    ->counts('permissions')
                    ->suffix(' шт.')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('id', 'desc')
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
