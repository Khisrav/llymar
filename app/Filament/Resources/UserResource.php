<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\UserResource\RelationManagers\ReceivedCommissionsRelationManager;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Пользователи';
    protected static ?string $navigationGroup = 'Пользователи';
    protected static ?string $pluralModelLabel = 'Пользователи';
    protected static ?string $pluralLabel = 'Пользователи';
    protected static ?string $modelLabel = 'Пользователь';
    protected static ?string $label = 'Пользователь';

    protected static ?int $navigationSort = 1;

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
            "Карачаево-Черкесская Республика", "Республика Карелия", "Республика Крым",
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

    /**
     * Get the current authenticated user
     */
    protected static function getCurrentUser(): ?User
    {
        return Auth::user();
    }

    /**
     * Check if current user has specified role
     */
    protected static function currentUserHasRole(string $role): bool
    {
        $user = static::getCurrentUser();
        return $user && $user->hasRole($role);
    }

    /**
     * Check if current user is Super Admin
     */
    protected static function isSuperAdmin(): bool
    {
        return static::currentUserHasRole('Super-Admin');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = static::getCurrentUser();
        if (!$user) {
            return parent::getEloquentQuery()->whereRaw('1 = 0'); // Return empty query if no user
        }
        
        if ($user->hasRole('Super-Admin')) {
            return parent::getEloquentQuery();
        }
        
        return parent::getEloquentQuery()->where('parent_id', $user->id);
    }

    public static function getNavigationLabel(): string
    {
        $user = static::getCurrentUser();
        if (!$user) return 'Пользователи';

        return match (true) {
            $user->hasRole('Operator') => 'Дилеры',
            $user->hasRole('Dealer') || $user->hasRole('ROP') => 'Менеджеры',
            default => 'Пользователи'
        };
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->description('Персональные данные и контактная информация')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        // Parent User Selection
                        Forms\Components\Select::make('parent_id')
                            ->label('Родитель')
                            ->helperText('Выберите родительского пользователя')
                            ->native(false)
                            ->searchable()
                            ->options(function () {
                                return Cache::remember('user_parent_options', 300, function () {
                                    if (static::isSuperAdmin()) {
                                        return User::orderBy('name')->pluck('name', 'id');
                                    } else {
                                        $currentUser = static::getCurrentUser();
                                        return $currentUser ? [$currentUser->id => $currentUser->name] : [];
                                    }
                                });
                            })
                            ->default(static::getCurrentUser()?->id)
                            ->required()
                            ->columnSpan(2),

                        // Personal Information
                        Forms\Components\TextInput::make('name')
                            ->label('ФИО')
                            ->placeholder('Введите полное имя')
                            ->required()
                            ->maxLength(255)
                            ->autocomplete('name'),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->placeholder('example@domain.com')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->autocomplete('email')
                            ->prefixIcon('heroicon-o-envelope'),

                        // Contact Information
                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->mask('+7 (999) 999 99-99')
                            ->placeholder('+7 (XXX) XXX XX-XX')
                            ->required()
                            ->prefixIcon('heroicon-o-phone'),

                        Forms\Components\TextInput::make('telegram')
                            ->label('Ник в Telegram')
                            ->startsWith('@')
                            ->placeholder('@username')
                            ->helperText('Ник должен начинаться с @')
                            ->prefixIcon('heroicon-o-chat-bubble-left-ellipsis'),

                        // Address Information
                        Grid::make(2)
                            ->columnSpan(2)
                            ->schema([
                                Forms\Components\Select::make('country')
                                    ->label('Страна')
                                    ->native(false)
                                    ->required()
                                    ->searchable()
                                    ->prefixIcon('heroicon-o-globe-americas')
                                    ->options([
                                        'Армения' => 'Армения',
                                        'Беларусь' => 'Беларусь',
                                        'Казахстан' => 'Казахстан',
                                        'Киргизия' => 'Киргизия',
                                        'Россия' => 'Россия',
                                    ])
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('region', null)),

                                Forms\Components\Select::make('region')
                                    ->label('Регион')
                                    ->native(false)
                                    ->required()
                                    ->searchable()
                                    ->disabled(fn (Get $get) => !$get('country'))
                                    ->options(function (Get $get) {
                                        $country = $get('country');
                                        return $country ? self::$countries[$country] : [];
                                    })
                                    ->helperText('Сначала выберите страну'),
                            ]),

                        Forms\Components\Textarea::make('address')
                            ->label('Фактический адрес')
                            ->placeholder('Введите полный адрес')
                            ->required()
                            ->maxLength(500)
                            ->rows(2)
                            ->columnSpan(2),
                    ]),

                Section::make('Бизнес информация')
                    ->description('Данные о компании и комиссиях')
                    ->icon('heroicon-o-building-office')
                    ->columns(3)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('company')
                            ->label('Контрагент')
                            ->placeholder('Название компании')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\TextInput::make('reward_fee')
                            ->label('Комиссия')
                            ->placeholder('0.00')
                            ->postfix('%')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->required()
                            ->helperText('Комиссионный процент от 0 до 100'),

                        Forms\Components\Select::make('default_factor')
                            ->label('Коэффициент по умолчанию')
                            ->native(false)
                            ->required()
                            ->default('kz')
                            ->visible(static::isSuperAdmin())
                            ->helperText('Применяется для расчетов')
                            ->options([
                                'kz' => 'KZ',
                                'k1' => 'K1',
                                'k2' => 'K2',
                                'k3' => 'K3',
                                'k4' => 'K4',
                            ])
                            ->columnSpan(static::isSuperAdmin() ? 1 : 0),
                    ]),

                Section::make('Безопасность и доступ')
                    ->description('Настройки пароля и ролей')
                    ->icon('heroicon-o-shield-check')
                    ->columns(2)
                    ->collapsible()
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Пароль')
                            ->password()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->dehydrated(fn ($state): bool => filled($state))
                            ->rule(Password::default())
                            ->helperText('Минимум 8 символов')
                            ->hiddenOn('edit')
                            ->revealable(),

                        Forms\Components\Select::make('roles')
                            ->label('Роли')
                            ->relationship('roles', 'name')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name ?: $record->name)
                            ->preload()
                            ->required()
                            ->native(false)
                            ->hidden(!static::isSuperAdmin())
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->hasRole('Dealer')) {
                                    $managerRole = \Spatie\Permission\Models\Role::where('name', 'Manager')->first();
                                    return $managerRole ? [$managerRole->id] : [];
                                }
                                return [];
                            })
                            ->helperText('Определяет права доступа пользователя')
                            ->searchable(),
                        
                        Forms\Components\Toggle::make('can_access_dxf')
                            ->label('Доступ к DXF')
                            ->helperText(function ($record) {
                                if ($record && $record->parent_id) {
                                    $parent = User::find($record->parent_id);
                                    if ($parent && $parent->hasRole('Dealer')) {
                                        return 'Доступ наследуется от родителя-дилера автоматически';
                                    }
                                }
                                return 'Определяет права доступа к DXF генерации';
                            })
                            // ->hidden(!static::isSuperAdmin())
                            ->disabled(function ($record) {
                                if (static::isSuperAdmin()) {
                                    return false;
                                }
                            
                                return true;
                            })
                            ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $record) {
                                if ($record) {
                                    $set('can_access_dxf', $record->can('access dxf'));
                                }
                                
                                if ($record && $record->parent_id) {
                                    $parent = User::find($record->parent_id);
                                    if ($parent && $parent->hasRole('Dealer')) {
                                        $set('can_access_dxf', $parent->can('access dxf'));
                                    }
                                }
                            })
                            ->afterStateUpdated(function (Forms\Get $get, bool $state, $record) {
                                if ($record) {
                                    $state
                                        ? $record->givePermissionTo('access dxf')
                                        : $record->revokePermissionTo('access dxf');
                                    
                                    // Sync DXF access to children if this user is a Dealer
                                    if ($record->hasRole('Dealer')) {
                                        $record->syncChildrenDxfAccess();
                                    }
                                }
                            })
                            ->default(function () {
                                $user = Auth::user();
                                if ($user && $user->hasRole('Dealer') && $user->can('access dxf')) {
                                    return true;
                                }
                                return false;
                            }),

                    ]),

                Section::make('Реквизиты')
                    ->description('Банковские и юридические данные')
                    ->icon('heroicon-o-banknotes')
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('inn')
                                    ->label('ИНН')
                                    ->placeholder('0000000000')
                                    ->maxLength(12)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('kpp')
                                    ->label('КПП')
                                    ->placeholder('000000000')
                                    ->maxLength(9)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('current_account')
                                    ->label('Расчетный счет')
                                    ->placeholder('00000000000000000000')
                                    ->maxLength(20)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('correspondent_account')
                                    ->label('Корреспондентский счет')
                                    ->placeholder('00000000000000000000')
                                    ->maxLength(20)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('bik')
                                    ->label('БИК')
                                    ->placeholder('000000000')
                                    ->maxLength(9)
                                    ->columnSpan(1),

                                Forms\Components\TextInput::make('bank')
                                    ->label('Банк')
                                    ->placeholder('Название банка')
                                    ->maxLength(255)
                                    ->columnSpan(3),

                                Forms\Components\Textarea::make('legal_address')
                                    ->label('Юридический адрес')
                                    ->placeholder('Полный юридический адрес')
                                    ->maxLength(500)
                                    ->rows(2)
                                    ->columnSpan(4),
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
                    ->size(Tables\Columns\TextColumn\TextColumnSize::ExtraSmall)
                    ->alignCenter(),

                Tables\Columns\ImageColumn::make('avatar')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF')
                    ->size(40),

                Tables\Columns\TextColumn::make('name')
                    ->label('Пользователь')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->email)
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->icon('heroicon-o-phone')
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company')
                    ->label('Компания')
                    ->searchable()
                    ->wrap()
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->company)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('country')
                    ->label('Страна')
                    ->badge()
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('default_factor')
                    ->label('Коэфф-т')
                    ->formatStateUsing(fn ($state) => strtoupper($state ?? 'kz'))
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'kz' => 'gray',
                        'k1' => 'blue',
                        'k2' => 'green',
                        'k3' => 'yellow',
                        'k4' => 'red',
                        default => 'gray',
                    })
                    ->visible(static::isSuperAdmin())
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('role')
                    ->label('Роль')
                    ->searchable()
                    ->formatStateUsing(function (Model $record) {
                        $role = $record->roles()->first();
                        return $role ? ($role->display_name ?: $role->name) : 'Нет роли';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Super-Admin' => 'danger',
                        'Operator' => 'warning',
                        'Manager' => 'success',
                        'ROP' => 'info',
                        default => 'gray',
                    })
                    ->hidden(!static::isSuperAdmin())
                    ->sortable(),

                Tables\Columns\TextColumn::make('reward_fee')
                    ->label('Комиссия')
                    ->suffix('%')
                    ->numeric(decimalPlaces: 2)
                    ->alignEnd()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\ToggleColumn::make('can_access_dxf')
                    ->label('DXF')
                    ->visible(static::isSuperAdmin())
                    ->disabled(function (Model $record) {
                        if ($record->parent_id) {
                            $parent = User::find($record->parent_id);
                            return $parent && $parent->hasRole('Dealer');
                        }
                        return false;
                    })
                    ->state(function (Model $record) {
                        return User::find($record->id)?->can('access dxf') ?? false;
                    })
                    ->afterStateUpdated(function (Model $record, bool $state) {
                        $user = User::find($record->id);
                        if ($user) {
                            $state ? $user->givePermissionTo('access dxf') : $user->revokePermissionTo('access dxf');
                            
                            // Sync DXF access to children if this user is a Dealer
                            if ($user->hasRole('Dealer')) {
                                $user->syncChildrenDxfAccess();
                            }
                        }
                    })
                    ->tooltip(function (Model $record) {
                        if ($record->parent_id) {
                            $parent = User::find($record->parent_id);
                            if ($parent && $parent->hasRole('Dealer')) {
                                return 'Доступ наследуется от родителя-дилера';
                            }
                        }
                        return 'Доступ к DXF файлам';
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создан')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->label('Роль')
                    ->native(false)
                    ->multiple()
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name ?: $record->name)
                    ->preload(),

                SelectFilter::make('country')
                    ->label('Страна')
                    ->native(false)
                    ->multiple()
                    ->options([
                        'Армения' => 'Армения',
                        'Беларусь' => 'Беларусь',
                        'Казахстан' => 'Казахстан',
                        'Киргизия' => 'Киргизия',
                        'Россия' => 'Россия',
                    ]),

                SelectFilter::make('default_factor')
                    ->label('Коэффициент')
                    ->native(false)
                    ->visible(static::isSuperAdmin())
                    ->options([
                        'kz' => 'KZ',
                        'k1' => 'K1',
                        'k2' => 'K2',
                        'k3' => 'K3',
                        'k4' => 'K4',
                    ]),

                Tables\Filters\Filter::make('has_dxf_access')
                    ->label('Доступ к DXF')
                    ->visible(static::isSuperAdmin())
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('permissions', fn ($q) => $q->where('name', 'access dxf'))
                    )
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->slideOver(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks();
    }

    public static function getRelations(): array
    {
        return [
            OrdersRelationManager::class,
            ReceivedCommissionsRelationManager::class,
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

    public static function getNavigationBadge(): ?string
    {
        $user = static::getCurrentUser();
        if (!$user) return null;
        
        return Cache::remember(
            "user_count_badge_{$user->id}", 
            300, 
            fn () => static::getEloquentQuery()->count()
        );
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone', 'company'];
    }
}
