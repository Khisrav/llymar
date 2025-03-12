<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Models\User;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static $countries = [
        "" => [],
        "Армения" => [
            "Арагацотн", "Арарат", "Армавир", "Гегаркуник", "Котайк",
            "Лори", "Ширак", "Сюник", "Тавуш", "Вайоц Дзор", "Ереван"
        ],
        "Беларусь" => [
            "Брестская область", "Гомельская область", "Гродненская область",
            "Минская область", "Могилевская область", "Витебская область", "Минск"
        ],
        "Казахстан" => [
            "Акмолинская область", "Актюбинская область", "Алматинская область",
            "Атырауская область", "Восточно-Казахстанская область",
            "Жамбылская область", "Карагандинская область", "Костанайская область",
            "Кызылординская область", "Мангистауская область",
            "Северо-Казахстанская область", "Павлодарская область",
            "Туркестанская область", "Западно-Казахстанская область", "Алматы",
            "Астана", "Шымкент"
        ],
        "Киргизия" => [
            "Баткенская область", "Чуйская область", "Джалал-Абадская область",
            "Нарынская область", "Ошская область", "Таласская область",
            "Иссык-Кульская область", "Бишкек", "Ош"
        ],
        "Россия" => [
            "Республика Адыгея", "Республика Башкортостан", "Республика Бурятия",
            "Республика Алтай", "Республика Дагестан", "Республика Ингушетия",
            "Кабардино-Балкарская Республика", "Республика Калмыкия",
            "Карачаево-Черкесская Республика", "Республика Карелия",
            "Республика Коми", "Республика Марий Эл", "Республика Мордовия",
            "Республика Саха (Якутия)", "Республика Северная Осетия-Алания",
            "Республика Татарстан", "Республика Тыва", "Удмуртская Республика",
            "Республика Хакасия", "Чеченская Республика", "Чувашская Республика",
            "Алтайский край", "Забайкальский край", "Камчатский край",
            "Краснодарский край", "Красноярский край", "Пермский край",
            "Приморский край", "Ставропольский край", "Хабаровский край",
            "Амурская область", "Архангельская область", "Астраханская область",
            "Белгородская область", "Брянская область", "Владимирская область",
            "Волгоградская область", "Вологодская область", "Воронежская область",
            "Ивановская область", "Иркутская область", "Калининградская область",
            "Калужская область", "Кемеровская область", "Кировская область",
            "Костромская область", "Курганская область", "Курская область",
            "Ленинградская область", "Липецкая область", "Магаданская область",
            "Московская область", "Мурманская область", "Нижегородская область",
            "Новгородская область", "Новосибирская область", "Омская область",
            "Оренбургская область", "Орловская область", "Пензенская область",
            "Псковская область", "Ростовская область", "Рязанская область",
            "Самарская область", "Саратовская область", "Сахалинская область",
            "Свердловская область", "Смоленская область", "Тамбовская область",
            "Тверская область", "Томская область", "Тульская область",
            "Тюменская область", "Ульяновская область", "Челябинская область",
            "Ярославская область", "Москва", "Санкт-Петербург", "Севастополь",
            "Еврейская автономная область", "Ненецкий автономный округ",
            "Ханты-Мансийский автономный округ", "Чукотский автономный округ",
            "Ямало-Ненецкий автономный округ"
        ]
    ];

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
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        Forms\Components\Select::make('country')
                            ->label('Страна')
                            ->native(false)
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->options([
                                'Армения' => 'Армения',
                                'Беларусь' => 'Беларусь',
                                'Казахстан' => 'Казахстан',
                                'Киргизия' => 'Киргизия',
                                'Россия' => 'Россия',
                            ])
                            ->live(),
                        Forms\Components\Select::make('region')
                            ->label('Регион')
                            ->native(false)
                            ->required()
                            ->searchable()
                            ->selectablePlaceholder(false)
                            ->options(function (Get $get) {
                                return self::$countries[$get('country')];
                            }),
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
                                    ->native(false)
                                    ->options(function () {
                                        return array_combine(
                                            array_map(fn($key) => 'KU' . $key, range(1, 10)),
                                            array_map(fn($key) => 'KU' . $key, range(1, 10))
                                        );
                                    })
                                    ->columnSpan(3),
                                Forms\Components\Select::make('wholesale_factor_key')
                                    ->label('Оптовый коэффициент')
                                    ->native(false)
                                    ->options(function () {
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
                                Forms\Components\TextInput::make('kpp')
                                    ->label('КПП')
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('current_account')
                                    ->columnSpan(2)
                                    ->label('Расчетный счет'),
                                Forms\Components\TextInput::make('correspondent_account')
                                    ->columnSpan(2)
                                    ->label('Корреспондентский счет'),
                                Forms\Components\TextInput::make('bik')
                                    ->columnSpan(2)
                                    ->label('БИК'),
                                Forms\Components\TextInput::make('bank')
                                    ->columnSpan(2)
                                    ->label('Банк'),
                                Forms\Components\TextInput::make('legal_address')
                                    ->columnSpan(2)
                                    ->label('Юридический адрес'),
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
                    ->relationship('roles', 'name'),
                SelectFilter::make('country')
                    ->label('Страна')
                    ->native(false)
                    ->options([
                        'Армения' => 'Армения',
                        'Беларусь' => 'Беларусь',
                        'Казахстан' => 'Казахстан',
                        'Киргизия' => 'Киргизия',
                        'Россия' => 'Россия',
                    ]),
                SelectFilter::make('region')
                    ->label('Регион')
                    ->native(false)
                    ->options(self::$countries),
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
