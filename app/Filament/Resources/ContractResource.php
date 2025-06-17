<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Договоры';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Параметры договора')
                    ->collapsible()
                    ->columns(4)
                    ->schema([
                        Select::make('counterparty_type')
                            ->label('Тип заказчика')
                            ->options([
                                'entrepreneur' => 'ИП',
                                'individual' => 'ФЛ',
                                'legal_entity' => 'ЮЛ',
                            ])
                            ->live()
                            ->native(false)
                            ->required(),
                        Select::make('template_id')
                            ->label('Шаблон договора')
                            ->native(false)
                            ->options(ContractTemplate::all()->pluck('name', 'id'))
                            ->required(),
                        Select::make('company_performer_id')
                            ->label('Организация исполнитель')
                            ->native(false)
                            ->options(Company::where('type', 'performer')->get()->pluck('short_name', 'id'))
                            ->required(),
                        Select::make('company_factory_id')
                            ->label('Завод')
                            ->native(false)
                            ->options(Company::where('type', 'factory')->get()->pluck('short_name', 'id'))
                            ->required(),
                        DatePicker::make('date')
                            ->label('Дата')
                            ->native(false)
                            ->required(),
                        Split::make([
                            TextInput::make('department_code')
                                ->label('Код подразделения')
                                ->type('number')
                                ->required(),
                            TextInput::make('number')
                                ->label('Номер')
                                // ->disabled()
                                ->type('number'),
                                // ->required(),
                            Select::make('index')
                                ->label('Индекс')
                                ->options([
                                    'МВ' => 'МВ',
                                    'ИК' => 'ИК',
                                    'ТК' => 'ТК',
                                ])
                                ->native(false)
                                ->default('МВ')
                                ->selectablePlaceholder(false)
                                ->required(),   
                        ])->columnSpan(2),
                        Select::make('order_id')
                            ->label('Договор для заказа')
                            ->native(false)
                            ->searchable()
                            ->options(Order::all()->pluck('order_number', 'id'))
                            ->optionsLimit(20)
                            ->default(fn () => request()->query('order_id'))
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $order = Order::find($state);
                                    if ($order) {
                                        $set('counterparty_fullname', $order->customer_name);
                                        $set('counterparty_phone', $order->customer_phone);
                                        $set('counterparty_email', $order->customer_email);
                                        $set('counterparty_address', $order->customer_address);
                                        $set('installation_address', $order->customer_address);
                                        $set('price', $order->total_price);
                                    }
                                }
                            })
                            ->required(),
                    ]),
                Section::make('Данные заказчика')
                    ->collapsible()
                    ->columns(3)
                    ->schema([
                        TextInput::make('counterparty_fullname')
                            ->label(fn ($get) => match($get('counterparty_type')) {
                                'legal_entity' => 'Наименование организации',
                                'entrepreneur' => 'ФИО индивидуального предпринимателя',
                                'individual' => 'ФИО физического лица',
                                default => 'Наименование'
                            })
                            ->required(),
                        TextInput::make('counterparty_address')
                            ->label('Адрес')
                            ->required(),
                        TextInput::make('counterparty_email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        TextInput::make('counterparty_phone')
                            ->label('Телефон')
                            ->tel()
                            ->required(),
                        TextInput::make('installation_address')
                            ->label('Адрес установки')
                            ->required()
                    ]),
                Section::make('Финансовые условия')
                    ->collapsible()
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('Цена')
                            ->numeric()
                            ->default(fn () => request()->query('total_price'))
                            ->required(),
                        TextInput::make('advance_payment_percentage')
                            ->label('Процент аванса')
                            ->numeric()
                            ->default(0)
                            ->suffix('%')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $counterparty_type = [
            'entrepreneur' => 'ИП',
            'individual' => 'ФЛ',
            'legal_entity' => 'ЮЛ',
        ];
        
        return $table
            ->columns([
            Tables\Columns\TextColumn::make('id')
                ->label('ID')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
            Tables\Columns\TextColumn::make('number')
                ->label('№')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false)
                ->formatStateUsing(fn ($record): HtmlString => new HtmlString("
                <div>{$record->department_code}-{$record->number}-{$record->index}</div>"))
                ->sortable(),
            Tables\Columns\TextColumn::make('counterparty_fullname')
                ->label('Заказчик')
                ->searchable()
                ->wrap()
                ->formatStateUsing(fn ($record): HtmlString => new HtmlString("
                    <div><strong>" . ($record->counterparty_fullname ?: 'Не указано') . "</strong></div>
                    <div><small>{$counterparty_type[$record->counterparty_type]} | {$record->counterparty_phone}</small></div>
                    <div><small>📧 {$record->counterparty_email}</small></div>
                    <div><small>📍 " . ($record->counterparty_address ?: 'Не указан') . "</small></div>"))
                ->toggleable(isToggledHiddenByDefault: false)
                ->sortable(),
            Tables\Columns\TextColumn::make('installation_address')
                ->label('Адрес установки')
                ->sortable()
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('price')
                ->label('Цена | Аванс')
                ->sortable()
                ->searchable()
                ->formatStateUsing(fn ($record): HtmlString => new HtmlString("
                <div>{$record->price}₽ | {$record->advance_payment_percentage}%</div>"))
                ->toggleable(isToggledHiddenByDefault: false),
            Tables\Columns\TextColumn::make('date')
                ->label('Дата')
                ->dateTime('d.m.Y')
                ->toggleable(isToggledHiddenByDefault: false)
                ->sortable(),
            Tables\Columns\TextColumn::make('companyPerformer.short_name')
                ->label('Организации')
                ->searchable()
                ->wrap()
                ->formatStateUsing(fn ($record): HtmlString => new HtmlString("
                    <div><strong>👷 Исполнитель:</strong></div>
                    <div><small>" . ($record->companyPerformer->short_name ?? 'Не указан') . "</small></div>
                    <div><strong>🏭 Завод:</strong></div>
                    <div><small>" . ($record->companyFactory->short_name ?? 'Не указан') . "</small></div>"))
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
            Tables\Columns\TextColumn::make('template.name')
                ->label('Связанные документы')
                ->searchable()
                ->wrap()
                ->formatStateUsing(fn ($record): HtmlString => new HtmlString("
                    <div><strong>📋 Шаблон:</strong></div>
                    <div><small>" . ($record->template->name ?? 'Не указан') . "</small></div>
                    <div><strong>📦 Заказ:</strong></div>
                    <div><small>" . ($record->order->order_number ?? 'Не указан') . "</small></div>"))
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
