<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\WholesaleFactor;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Пользователи';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ФИО')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('address')
                            ->label('Фактический адрес')
                            ->required(),
                        Forms\Components\TextInput::make('company')
                            ->label('Организация/Компания')
                            ->required(),
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->mask('+7 (999) 999 99-99')
                                    ->required()
                                    ->columnSpan(1),
                                    
                                Forms\Components\Select::make('role')
                                    ->label('Роль')
                                    ->options([
                                        'admin' => 'Администратор',
                                        'manager' => 'Менеджер',
                                        'user' => 'Пользователь',
                                    ])
                                    ->required()
                                    ->columnSpan(1),
                                    
                                Forms\Components\Select::make('reduction_factor_key')
                                    ->label('Коэффициент уменьшения')
                                    ->native(true)
                                    ->options(function () {
                                        // Dynamically generate 'KU1', 'KU2', ..., 'KU10'
                                        return array_combine(
                                            array_map(fn($key) => 'KU' . $key, range(1, 10)),
                                            array_map(fn($key) => 'KU' . $key, range(1, 10))
                                        );
                                    })
                                    ->columnSpan(1),
                                
                                Forms\Components\Select::make('wholesale_factor_key')
                                    ->label('Оптовый коэффициент')
                                    ->native(true)
                                    ->options(function () {
                                        // Retrieve key-value pairs (e.g., name => value) from the WholesaleFactors model
                                        return \App\Models\WholesaleFactor::query()->pluck('name', 'name')->toArray();
                                    })
                                    ->columnSpan(1),
                                ]), 
                                             
                    ]),
                Section::make('Реквизиты')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('inn')
                                    ->label('ИНН')
                                    ->columnSpan(1)
                                    ->required(),
                                Forms\Components\TextInput::make('kpp')
                                    ->label('КПП')
                                    ->columnSpan(1)
                                    ->required(),
                                Forms\Components\TextInput::make('current_account')
                                    ->columnSpan(2)
                                    ->label('Расчетный счет')
                                    ->required(),
                                Forms\Components\TextInput::make('correspondent_account')
                                    ->columnSpan(2)
                                    ->label('Корреспондентский счет')
                                    ->required(),
                                Forms\Components\TextInput::make('bik')
                                    ->columnSpan(2)
                                    ->label('БИК')
                                    ->required(),
                                Forms\Components\TextInput::make('bank')
                                    ->columnSpan(2)
                                    ->label('Банк')
                                    ->required(),
                                Forms\Components\TextInput::make('legal_address')
                                    ->columnSpan(2)
                                    ->label('Юридический адрес')
                                    ->required(),
                        ])
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('role')
                    ->label('Роль')
                    ->searchable()
                    ->options([
                        'admin' => 'Администратор',
                        'manager' => 'Менеджер',
                        'user' => 'Пользователь',
                    ])
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Роль')
                    ->options([
                        'admin' => 'Администратор',
                        'manager' => 'Менеджер',
                        'user' => 'Пользователь',
                    ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
