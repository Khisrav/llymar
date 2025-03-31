<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use App\Models\ContractTemplate;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

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
                    ->columns(3)
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
                    ]),
                Section::make('Данные заказчика')
                    ->collapsible()
                    ->columns(3)
                    ->schema([
                        TextInput::make('counterparty_fullname')
                            ->label('Наименование')
                            ->hidden(fn ($get) => $get('counterparty_type') === 'legal_entity')
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
