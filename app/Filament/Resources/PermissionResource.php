<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
use App\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Разрешения';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralModelLabel = 'Разрешения';
    protected static ?string $pluralLabel = 'Разрешения';
    protected static ?string $modelLabel = 'Разрешение';
    protected static ?string $label = 'Разрешение';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('display_name')
                    ->label('Отображается как')
                    ->required()
                    ->placeholder('Введите отображаемое название')
                    ->helperText('Название разрешения, которое будет отображаться пользователям')
                    ->maxLength(255),
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
                Tables\Columns\TextInputColumn::make('display_name')
                    ->label('Отображается как')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                //guard_name
                Tables\Columns\TextColumn::make('guard_name')
                    ->label('Защитник')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'web' => 'info',
                        'api' => 'gray',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                //filter by guard_name
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Защитник')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ])
                    ->native(false),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(), // Temporarily disabled
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
            'index' => Pages\ListPermissions::route('/'),
            // 'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }
}
