<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommercialOfferResource\Pages;
use App\Models\CommercialOffer;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\KeyValue;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CommercialOfferResource extends Resource
{
    protected static ?string $model = CommercialOffer::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static ?string $navigationLabel = 'КП';
        
    public static function form(Form $form): Form
    {
        $openingTypes = [
            'left' => 'Левый проем',
            'right' => 'Правый проем',
            'center' => 'Центральный проем',
            'inner-left' => 'Входная группа левая',
            'inner-right' => 'Входная группа правая',
            'blind-glazing' => 'Глухое остекление',
            'triangle' => 'Треугольник',
        ];
    
        return $form
            ->schema([
                Section::make('Основная информация')
                    ->description('Базовые данные коммерческого предложения')
                    ->icon('heroicon-o-document')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Ответственный')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->default(Auth::id())
                                    ->required()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('selected_factor')
                                    ->label('Выбранный коэффициент')
                                    // ->default('kz')
                                    ->columnSpan(1),
                            ]),
                        
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_price')
                                    ->label('Общая стоимость')
                                    ->numeric()
                                    ->prefix('₽')
                                    ->required()
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('markup_percentage')
                                    ->label('Процент наценки')
                                    ->numeric()
                                    ->prefix('%')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Информация о заказчике')
                    ->description('Контактные данные и информация о заказчике')
                    ->icon('heroicon-o-user')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Имя заказчика')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Телефон заказчика')
                                    ->tel()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            
                            Forms\Components\Textarea::make('customer_address')
                                ->label('Адрес заказчика')
                                ->rows(2)
                                ->columnSpan(1),
                                
                            Forms\Components\Textarea::make('customer_comment')
                                ->label('Комментарий заказчика')
                                ->rows(2)
                                ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Информация о производителе')
                    ->description('Контактные данные производителя')
                    ->icon('heroicon-o-building-office')
                    ->collapsible()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('manufacturer_name')
                                    ->label('Название производителя')
                                    ->maxLength(255)
                                    ->columnSpan(1),
                                    
                                Forms\Components\TextInput::make('manufacturer_phone')
                                    ->label('Телефон производителя')
                                    ->tel()
                                    ->maxLength(255)
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Проемы')
                    ->description('Информация о проемах для изготовления')
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsible()
                    ->schema([
                        Repeater::make('openings')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        Forms\Components\Select::make('type')
                                            ->label('Тип')
                                            ->options($openingTypes)
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('doors')
                                            ->label('Створки')
                                            ->numeric()
                                            ->default(1)
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('width')
                                            ->label('Ширина (мм)')
                                            ->numeric()
                                            ->suffix('мм')
                                            ->columnSpan(1),
                                            
                                        Forms\Components\TextInput::make('height')
                                            ->label('Высота (мм)')
                                            ->numeric()
                                            ->suffix('мм')
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->defaultItems(1)
                            ->addActionLabel('Добавить проем')
                            ->addable(false)
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['type'] ? "{$openingTypes[$state['type']]} ({$state['width']}×{$state['height']}) {$state['doors']} ств." : 'Новый проем'),
                    ]),

                Section::make('Дополнительные товары')
                    ->description('Список дополнительных товаров (количества хранятся в корзине)')
                    ->icon('heroicon-o-plus-circle')
                    ->collapsible()
                    ->schema([
                        Repeater::make('additional_items')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('id')
                                    ->label('Товар')
                                    ->options(Item::where('is_for_llymar', true)->pluck('name', 'id'))
                                    ->searchable()
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Добавить товар')
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['id'] 
                                    ? (Item::find($state['id'])?->name ?? 'Неизвестный товар')
                                    : 'Новый товар'
                            ),
                    ]),

                Section::make('Стекло')
                    ->description('Информация о выбранном стекле (количество хранится в корзине)')
                    ->icon('heroicon-o-square-3-stack-3d')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Select::make('glass.id')
                            ->label('Тип стекла')
                            ->options(Item::where('category_id', 1)->pluck('name', 'id'))
                            ->searchable(),
                    ]),

                Section::make('Услуги')
                    ->description('Список дополнительных услуг (количества хранятся в корзине)')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->collapsible()
                    ->schema([
                        Repeater::make('services')
                            ->label('')
                            ->schema([
                                Forms\Components\Select::make('id')
                                    ->label('Услуга')
                                    ->options(Item::whereIn('category_id', [26, 35])->pluck('name', 'id'))
                                    ->searchable()
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel('Добавить услугу')
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => 
                                $state['id'] ? Item::find($state['id'])?->name ?? 'Неизвестная услуга' : 'Новая услуга'
                            ),
                    ]),

                Section::make('Товары в корзине')
                    ->description('Здесь хранятся количества для всех товаров (из дополнительных товаров, стекла и услуг)')
                    ->icon('heroicon-o-shopping-cart')
                    ->collapsible()
                    ->schema([
                        Repeater::make('cart_items')
                            ->label('')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Forms\Components\Select::make('item_id')
                                            ->label('Товар')
                                            ->options(Item::all()->pluck('name', 'id'))
                                            ->searchable()
                                            ->columnSpan(1)
                                            ->required(),
                                            
                                        Forms\Components\TextInput::make('quantity')
                                            ->label('Количество')
                                            ->numeric()
                                            ->step(0.1)
                                            ->default(1)
                                            ->columnSpan(1),
                                            
                                        Forms\Components\Toggle::make('checked')
                                            ->label('Активен')
                                            ->default(true)
                                            ->columnSpan(1),
                                    ]),
                            ])
                            ->addActionLabel('Добавить товар в корзину')
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => 
                                isset($state['item_id']) 
                                    ? (Item::find($state['item_id'])?->name ?? 'Неизвестный товар') . ' – ' . ($state['quantity'] ?? '?') . ' ' . (Item::find($state['item_id'])?->unit ?? 'шт.')
                                    : 'Новый товар в корзине'
                            )
                            ->mutateDehydratedStateUsing(function ($state): array {
                                // Transform repeater format back to associative array when saving
                                $transformed = [];
                                foreach ($state as $item) {
                                    if (isset($item['item_id'])) {
                                        $transformed[(string) $item['item_id']] = [
                                            'quantity' => (float) ($item['quantity'] ?? 1),
                                            'checked' => (bool) ($item['checked'] ?? true),
                                        ];
                                    }
                                }
                                return $transformed;
                            })
                            ->afterStateHydrated(function ($component, $state) {
                                if (is_array($state) && !empty($state)) {
                                    // Transform the cart_items associative array to repeater format when loading
                                    $transformed = [];
                                    foreach ($state as $itemId => $itemData) {
                                        $transformed[] = [
                                            'item_id' => $itemId,
                                            'quantity' => $itemData['quantity'] ?? 1,
                                            'checked' => $itemData['checked'] ?? true,
                                        ];
                                    }
                                    $component->state($transformed);
                                }
                            }),
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
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Ответственный')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('customer_name')
                    ->label('Заказчик')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 30 ? $state : null;
                    }),
                    
                Tables\Columns\TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('manufacturer_name')
                    ->label('Производитель')
                    ->searchable()
                    ->toggleable()
                    ->limit(25),
                    
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Стоимость')
                    ->badge()
                    ->color('gray')
                    ->money('RUB')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('markup_percentage')
                    ->label('Наценка')
                    ->suffix('%')
                    ->badge()
                    ->color('red')
                    ->numeric(decimalPlaces: 1)
                    ->sortable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('selected_factor')
                    ->label('Коэф.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kz' => 'success',
                        'pz' => 'warning',
                        'k1', 'k2', 'k3', 'k4' => 'info',
                        'p1', 'p2', 'p3' => 'danger',
                        'pr' => 'gray',
                        default => 'secondary',
                    })
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('openings_count')
                    ->label('Проемов')
                    ->getStateUsing(fn (CommercialOffer $record): int => count($record->openings ?? []))
                    ->badge()
                    ->color('primary')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('items_count')
                    ->label('Товаров')
                    ->getStateUsing(fn (CommercialOffer $record): int => count($record->cart_items ?? []))
                    ->badge()
                    ->color('info')
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Ответственный')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                    
                SelectFilter::make('selected_factor')
                    ->label('Коэффициент')
                    ->options([
                        'kz' => 'KZ',
                        'pz' => 'PZ',
                        'k1' => 'K1',
                        'k2' => 'K2',
                        'k3' => 'K3',
                        'k4' => 'K4',
                        'p1' => 'P1',
                        'p2' => 'P2',
                        'p3' => 'P3',
                        'pr' => 'PR',
                    ]),
                    
                Tables\Filters\Filter::make('has_customer')
                    ->label('С заказчиком')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('customer_name')),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                //     ->label('Просмотр'),
                // Tables\Actions\EditAction::make()
                //     ->label('Редактировать'),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Скачать PDF')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->url(fn (CommercialOffer $record): string => 
                        route('app.commercial_offers.pdf', $record)
                    )
                    ->openUrlInNewTab(),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Удалить выбранные'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListCommercialOffers::route('/'),
            'create' => Pages\CreateCommercialOffer::route('/create'),
            'view' => Pages\ViewCommercialOffer::route('/{record}'),
            // 'edit' => Pages\EditCommercialOffer::route('/{record}/edit'),
        ];
    }
    
    protected static function getCurrentUser(): ?User
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user;
    }

    protected static function isSuperAdmin(): bool
    {
        $user = static::getCurrentUser();
        return $user && $user->hasRole('Super-Admin');
    }

    public static function getNavigationBadge(): ?string
    {
        if (!static::isSuperAdmin()) {
            return null;
        }
        
        return static::getModel()::count();
    }
}
