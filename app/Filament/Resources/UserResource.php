<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
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
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        if ($user->hasRole('Super-Admin')) return parent::getEloquentQuery();
        return parent::getEloquentQuery()->where('parent_id', $user->id);
    }
    
    public static function getNavigationLabel(): string
    {
        $user = auth()->user();
        
        if ($user->hasRole('Operator')) return 'Менеджеры';
        else if ($user->hasRole('Manager')) return 'Дилеры';
        
        return 'Пользователи';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('Родитель')
                            ->native(false)
                            ->searchable()
                            ->hidden(!auth()->user()->hasRole('Super-Admin'))
                            ->options(function () {
                                // $users = User::all()->map(function ($user) {
                                //     return sprintf('%s - %s', $user->id, $user->name);
                                // });
                                // return $users->all();
                                return User::all()->pluck('name', 'id');
                            })
                            ->required(),
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
                        Forms\Components\TextInput::make('reward_fee')
                            ->label('Комиссия')
                            ->postfix('%')
                            ->type('number')
                            // ->step(1)
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Grid::make(9)
                            ->schema([
                                Forms\Components\TextInput::make('password')
                                    ->label('Пароль')
                                    ->password()
                                    ->required()
                                    ->hiddenOn('edit')
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('telegram')
                                    ->label('Ник в Telegram')
                                    ->startsWith('@')
                                    ->placeholder('@user')
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('phone')
                                    ->label('Телефон')
                                    ->mask('+7 (999) 999 99-99')
                                    ->required()
                                    ->columnSpan(3),

                                Forms\Components\Select::make('roles')
                                    ->label('Роли')
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->hidden(!auth()->user()->hasRole('Super-Admin'))
                                    ->columnSpan(3),
                                    
                                Forms\Components\Select::make('reduction_factor_key')
                                    ->label('Коэффициент уменьшения')
                                    ->native(true)
                                    ->options(function () {
                                        return array_combine(
                                            array_map(fn($key) => 'KU' . $key, range(1, 10)),
                                            array_map(fn($key) => 'KU' . $key, range(1, 10))
                                        );
                                    })
                                    ->columnSpan(3),
                                
                                Forms\Components\Select::make('wholesale_factor_key')
                                    ->label('Оптовый коэффициент')
                                    ->native(true)
                                    ->options(function () {
                                        // Retrieve key-value pairs (e.g., name => value) from the WholesaleFactors model
                                        return \App\Models\WholesaleFactor::query()->pluck('name', 'name')->toArray();
                                    })
                                    ->columnSpan(3),
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
                                    ->columnSpan(1),
                                    // ->required(),
                                Forms\Components\TextInput::make('kpp')
                                    ->label('КПП')
                                    ->columnSpan(1),
                                    // ->required(),
                                Forms\Components\TextInput::make('current_account')
                                    ->columnSpan(2)
                                    ->label('Расчетный счет'),
                                    // ->required(),
                                Forms\Components\TextInput::make('correspondent_account')
                                    ->columnSpan(2)
                                    ->label('Корреспондентский счет'),
                                    // ->required(),
                                Forms\Components\TextInput::make('bik')
                                    ->columnSpan(2)
                                    ->label('БИК'),
                                    // ->required(),
                                Forms\Components\TextInput::make('bank')
                                    ->columnSpan(2)
                                    ->label('Банк'),
                                    // ->required(),
                                Forms\Components\TextInput::make('legal_address')
                                    ->columnSpan(2)
                                    ->label('Юридический адрес'),
                                    // ->required(),
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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->searchable()
                    ->formatStateUsing(fn (Model $record) => $record->getRoleNames()->first())
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Роль')
                    ->native(false)
                    ->relationship('roles', 'name')
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
            OrdersRelationManager::class,
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
