<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ItemProperty;

class ItemPropertiesRelationManager extends RelationManager
{
    protected static string $relationship = 'itemProperties';
    protected static ?string $title = 'Свойства';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Наименование')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('value')
                    ->label('Значение')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Наименование')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextInputColumn::make('value')
                    ->label('Значение')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_default_properties')
                    ->label('Создать свойства')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    // ->hidden(fn () => !str_contains(optional($this->getOwnerRecord()->category?->name)->__toString() ?? '', 'Ручки'))
                    ->action(function () {
                        $record = $this->getOwnerRecord();
                        
                        // Check if properties already exist
                        $existingProperties = $record->itemProperties()->whereIn('name', ['MP', 'd', 'g', 'i'])->count();
                        
                        if ($existingProperties > 0) {
                            \Filament\Notifications\Notification::make()
                                ->title('Внимание')
                                ->body('У этого товара уже есть некоторые свойства. Будут добавлены только отсутствующие.')
                                ->warning()
                                ->send();
                        }
                        
                        // Get existing property names
                        $existingNames = $record->itemProperties()->whereIn('name', ['MP', 'd', 'g', 'i'])->pluck('name')->toArray();
                        
                        // Properties to create
                        $propertiesToCreate = ['MP', 'd', 'g', 'i'];
                        
                        // Filter out existing properties
                        $newProperties = array_diff($propertiesToCreate, $existingNames);
                        
                        if (empty($newProperties)) {
                            \Filament\Notifications\Notification::make()
                                ->title('Информация')
                                ->body('Все свойства уже существуют.')
                                ->info()
                                ->send();
                            return;
                        }
                        
                        // Create new properties
                        foreach ($newProperties as $propertyName) {
                            ItemProperty::create([
                                'item_id' => $record->id,
                                'name' => $propertyName,
                                'value' => '0'
                            ]);
                        }
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Успешно')
                            ->body('Свойства товара созданы: ' . implode(', ', $newProperties))
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Создать свойства товара')
                    ->modalDescription('Будут созданы свойства MP, d, g, i со значением по умолчанию 0.')
                    ->modalSubmitActionLabel('Создать'),
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