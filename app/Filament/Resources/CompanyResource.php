<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Filament\Resources\CompanyResource\RelationManagers;
use App\Filament\Resources\CompanyResource\RelationManagers\CompanyBillsRelationManager;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Контрагенты';
    protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section: Company Information
                Section::make('')
                    ->schema([
                        Select::make('type')
                            ->label('Тип контрагента')
                            ->options([
                                'performer' => 'Исполнитель',
                                'factory' => 'Завод',
                                'supplier' => 'Поставщик',
                                'customer' => 'Заказчик',
                            ])
                            ->default('performer')
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->required(),
                        TextInput::make('short_name')
                            ->label('Короткое название')
                            ->required(),
                        TextInput::make('full_name')
                            ->label('Полное название')
                            ->required(),
                        
                        TextInput::make('boss_name')
                            ->label('ФИО руководителя')
                            ->required(),
                        Select::make('boss_title')
                            ->label('Должность руководителя')
                            ->options([
                                'director' => 'Директор',
                                'ceo' => 'Генеральный директор',
                                'chief' => 'Начальник',
                                'supervisor' => 'Руководитель',
                            ])
                            ->default('director')
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->required(),
                        
                        TextInput::make('legal_address')
                            ->label('Юридический адрес')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->type('email')
                            ->required(),
                        TextInput::make('phone')
                            ->label('Телефон')
                            ->mask('+7 (999) 999 99-99')
                            ->placeholder('+7 (___) ___ __-__')
                            ->required(),
                        TextInput::make('phone_2')
                            ->label('Телефон 2')
                            ->placeholder('+7 (___) ___ __-__')
                            ->mask('+7 (999) 999 99-99'),
                        TextInput::make('phone_3')
                            ->label('Телефон 3')
                            ->placeholder('+7 (___) ___ __-__')
                            ->mask('+7 (999) 999 99-99'),
                        TextInput::make('website')
                            ->label('Сайт')
                            ->placeholder('https://example.com')
                            ->url(),
                        
                        TextInput::make('inn')
                            ->label('ИНН')
                            ->required(),
                        TextInput::make('kpp')
                            ->label('КПП')
                            ->required(),
                        TextInput::make('ogrn')
                            ->label('ОГРН')
                            ->required(),
                        Select::make('vat')
                            ->label('НДС')
                            ->options([
                                0 => 'Без НДС',
                                5 => '5%',
                                7 => '7%',
                                10 => '10%',
                                20 => '20%',
                            ])
                            ->default(0)
                            ->native(false)
                            ->selectablePlaceholder(false)
                            ->required(),
                    ])->columns(3),
            ]);
    }


    public static function table(Table $table): Table
    {
        $company_type = [
            'performer' => 'Исполнитель',
            'factory' => 'Завод',
            'supplier' => 'Поставщик',
            'customer' => 'Заказчик',
        ];
        
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('type')
                    ->label('Тип контрагента')
                    // ->searchable()
                    // ->sortable()
                    ->html()
                    ->formatStateUsing(fn ($record): HtmlString => new HtmlString("<div class='text-sm font-medium text-gray-900'>{$company_type[$record->type]}</div>НДС: {$record->vat}%"))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('short_name')
                    ->label('Короткое название')
                    ->searchable()
                    // ->sortable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('full_name')
                    ->label('Полное название')
                    ->searchable()
                    // ->sortable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('boss_name')
                    ->label('Руководитель')
                    // ->searchable()
                    // ->sortable()
                    ->wrap()
                    ->formatStateUsing(fn ($record): HtmlString => new HtmlString("{$record->boss_title}<br><span>{$record->boss_name}</span>"))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('legal_address')
                    ->label('Юр. адрес')
                    ->searchable()
                    // ->sortable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('email')
                    ->label('Контакты')
                    // ->searchable()
                    // ->wrap()
                    ->html()
                    ->formatStateUsing(fn ($record): HtmlString => new HtmlString("{$record->email}<br>{$record->phone}"))
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('inn')
                    ->label('Реквизиты')
                    ->html()
                    ->formatStateUsing(fn ($record): HtmlString => new HtmlString("ИНН: {$record->inn}<br>КПП: {$record->kpp}<br>ОГРН: {$record->ogrn}"))
            ])
            ->filters([
                //
            ])
            ->actions([
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
            CompanyBillsRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }
}
