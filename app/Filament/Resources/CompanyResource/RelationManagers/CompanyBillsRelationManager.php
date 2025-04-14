<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CompanyBillsRelationManager extends RelationManager
{
    protected static string $relationship = 'companyBills';
    protected static ?string $title = 'Счета контрагента';
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['company_id'] = $this->getOwnerRecord()->id;
        return $data;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('current_account')
                    ->label('Расчетный счет')
                    ->required(),
                Forms\Components\TextInput::make('correspondent_account')
                    ->label('Корреспондентский счет')
                    ->required(),
                Forms\Components\TextInput::make('bank_name')
                    ->label('Название банка')
                    ->required(),
                Forms\Components\TextInput::make('bank_address')
                    ->label('Адрес банка')
                    ->required(),
                Forms\Components\TextInput::make('bik')
                    ->label('БИК')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('company_id')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('bank_name')
                    ->label('Название банка')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('current_account')
                    ->label('Расчетный счет')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('correspondent_account')
                    ->label('Корреспондентский счет')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('bank_address')
                    ->label('Адрес банка')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('bik')
                    ->label('БИК')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
