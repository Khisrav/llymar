<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderJournalResource\Pages;
use App\Filament\Resources\OrderJournalResource\RelationManagers;
use App\Http\Controllers\SketchController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\LogisticsCompany;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn\IconColumnSize;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class OrderJournalResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'ЦЕХ';
    protected static ?string $pluralModelLabel = 'ЦЕХ';
    // protected static ?string $navigationGroup = 'Заказы';
    
    // Order status constants for consistency
    public const ORDER_STATUSES = [
        'created' => 'Создан',
        'paid' => 'Оплачен', 
        'expired' => 'Просрочен',
        'assembled' => 'Собран',
        'sent' => 'Отправлен',
        'completed' => 'Завершен',
        'archived' => 'Архивирован',
        'unknown' => 'Неизвестно'
    ];
    
    // Status colors for badges
    public const ORDER_STATUS_COLORS = [
        'created' => 'cyan',
        'paid' => 'success',
        'expired' => 'danger',
        'assembled' => 'info',
        'sent' => 'warning',
        'completed' => 'success',
        'archived' => 'gray',
        'unknown' => 'danger'
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['orderItems.item', 'orderOpenings.doorHandle'])
                      ->where('status', 'paid');
            })
            ->recordClasses(fn (Model $record): string => match ($record->id) {
                default => ' khisrav',
            })
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label(fn () => new HtmlString('№ <br> Дата'))
                    ->formatStateUsing(function ($state, $record): string {
                        $number = explode('-', $state);
                        $color = $number[0] == '1' ? 'cyan' : ($number[0] == '4' ? 'gray' : 'success');
                        
                        $days = date_diff(date_create($record->when_started_working_on_it), date_create(now()));
                        $days_str = $days->days > 0 ? '| ' . $days->days . 'дн.' : '';
                        
                        return '<span style="border-color: ' . ($color == 'cyan' ? '#06b6d4' : ($color == 'gray' ? '#6b7280' : '#10b981')) . ';border-width: 1px;border-style: solid;color: ' . ($color == 'cyan' ? '#06b6d4' : ($color == 'gray' ? '#6b7280' : '#10b981')) . '; padding: 2px 6px;border-radius: 6px;">' . $state . '</span><br>' . $record->created_at->format('d.m.Y') . ' ' . $days_str;
                    })
                    // ->wrap()
                    ->html()
                    ->sortable()
                    ->tooltip(fn ($record): string => $record->created_at->format('d M Y H:i'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer_address')
                    ->label('Адрес')
                    ->searchable()
                    ->wrap()
                    ->formatStateUsing(function (?Model $record) {
                        $prefix = explode('-', $record->order_number)[0];
                        if ($prefix == '1') {
                            return $record->city . ' / ' . (LogisticsCompany::find($record->logistics_company_id)->name ?? '');
                        } else if ($prefix == '4') {
                            return $record->customer_address;
                        } else if ($prefix == '6') {
                            return 'Самовывоз';
                        } else return $record->customer_address;
                    })
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('xs')
                    ->limit(50),
                
                Tables\Columns\TextColumn::make('glass_code')
                    ->label('Стекло')
                    ->searchable()
                    ->suffix(fn (?Model $record): HtmlString => new HtmlString(
                        '<br>' . SketchController::calculateGlassArea($record->id)['total_area_m2'] . 'м²'
                    ))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('xs')
                    ->html()
                    ->wrap(),

                Tables\Columns\TextColumn::make('handles')
                    ->label('Ручки')
                    ->searchable()
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('xs')
                    ->wrap(),
                    
                Tables\Columns\SelectColumn::make('cut_status')
                    ->label('Распил')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->options(function (Model $record) {
                        $hasMilling = $record->orderItems->contains(fn ($item) => $item->item_id == 388);
                        return ($hasMilling ? ['Фрезеровка' => 'Фрезеровка'] : ['Сборка' => 'Сборка']) + ['Готово' => 'Готово'];
                    }),
                    
                Tables\Columns\IconColumn::make('is_painted')
                    ->label('Покраска')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->color(fn ($record) => $record->ral_code ? ($record->is_painted ? 'success' : 'danger') : 'gray')
                    ->boolean()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->action(
                        Tables\Actions\Action::make('toggle_painted')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Подтверждение')
                            ->modalDescription('Вы уверены, что хотите изменить статус покраски?')
                            ->modalSubmitActionLabel('Да, изменить')
                            ->modalCancelActionLabel('Отмена')
                            ->action(function ($record) {
                                $record->update(['is_painted' => !$record->is_painted]);
                            })
                            ->disabled(fn ($record) => !$record || !$record->ral_code)
                    ),
                    
                Tables\Columns\SelectColumn::make('glass_acceptance')
                    ->label('Прием стекла')
                    ->wrapHeader()
                    ->disabled(fn ($record) => !$record->getGlassCodeAttribute())
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->options([
                        'Рекламация' => 'Рекламация',
                        'Переделка' => 'Переделка',
                        'Готово' => 'Готово',
                    ]),
                    
                Tables\Columns\IconColumn::make('is_sworn')
                    ->label('Поклейка')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->boolean()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->color(fn ($record) => $record->orderItems->contains(fn ($item) => $item->item_id == 389) 
                        ? ($record->is_sworn ? 'success' : 'danger') 
                        : 'gray')
                    ->action(
                        Tables\Actions\Action::make('toggle_sworn')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Подтверждение')
                            ->modalDescription('Вы уверены, что хотите изменить статус поклейки?')
                            ->modalSubmitActionLabel('Да, изменить')
                            ->modalCancelActionLabel('Отмена')
                            ->action(function ($record) {
                                $record->update(['is_sworn' => !$record->is_sworn]);
                            })
                            ->disabled(fn ($record) => !$record->orderItems->contains(fn ($item) => $item->item_id == 389))
                    ),
                    
                Tables\Columns\IconColumn::make('is_packed')
                    ->label('Упаковка')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->boolean()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->color(fn ($record) => str_starts_with($record->order_number, '4-') ? 'gray' : null)
                    ->action(
                        Tables\Actions\Action::make('toggle_packed')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Подтверждение')
                            ->modalDescription('Вы уверены, что хотите изменить статус упаковки?')
                            ->modalSubmitActionLabel('Да, изменить')
                            ->modalCancelActionLabel('Отмена')
                            ->action(function ($record) {
                                $record->update(['is_packed' => !$record->is_packed]);
                            })
                            ->disabled(fn ($record) => str_starts_with($record->order_number, '4-'))
                    ),
                    
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Завершен')
                    ->alignCenter()
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->boolean()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->color(function ($record) {
                        // Check if all conditions are met for completion
                        $paintAvailable = $record->ral_code; // Paint is available if RAL code exists
                        $swornAvailable = $record->orderItems->contains(fn ($item) => $item->item_id == 389); // Sworn available if item 389 exists
                        $packAvailable = !str_starts_with($record->order_number, '4-'); // Pack available if not order type 4
                        $glassAvailable = (bool)$record->getGlassCodeAttribute(); // Glass available if glass code exists
                        
                        $allStepsComplete = ($paintAvailable ? $record->is_painted : true) && 
                                          ($swornAvailable ? $record->is_sworn : true) && 
                                          ($packAvailable ? $record->is_packed : true) && 
                                          ($glassAvailable ? $record->glass_acceptance === 'Готово' : true) && 
                                          $record->cut_status === 'Готово';
                        
                        if ($allStepsComplete) {
                            return $record->is_completed ? 'success' : 'danger'; // Red when ready but not completed
                        }
                        
                        return 'gray'; // Gray when not all steps are complete
                    })
                    ->action(
                        Tables\Actions\Action::make('toggle_completed')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Подтверждение')
                            ->modalDescription('Вы уверены, что хотите изменить статус завершения заказа?')
                            ->modalSubmitActionLabel('Да, изменить')
                            ->modalCancelActionLabel('Отмена')
                            ->action(function ($record, $livewire) {
                                $newCompletedStatus = !$record->is_completed;
                                
                                // Update is_completed
                                $record->update(['is_completed' => $newCompletedStatus]);
                                
                                // Update status based on order type when setting to completed
                                if ($newCompletedStatus) {
                                    if (str_starts_with($record->order_number, '1-')) {
                                        $record->update(['status' => 'sent']);
                                    } else if (str_starts_with($record->order_number, '4-') || str_starts_with($record->order_number, '6-')) {
                                        $record->update(['status' => 'completed']);
                                    }
                                    
                                    // Refresh the table to hide the completed order
                                    $livewire->dispatch('$refresh');
                                }
                            })
                            ->disabled(function ($record) {
                                // Check availability of each step
                                $paintAvailable = $record->ral_code;
                                $swornAvailable = $record->orderItems->contains(fn ($item) => $item->item_id == 389);
                                $packAvailable = !str_starts_with($record->order_number, '4-');
                                $glassAvailable = (bool)$record->getGlassCodeAttribute();
                                
                                // Disable if not all available steps are complete
                                return !(($paintAvailable ? $record->is_painted : true) && 
                                        ($swornAvailable ? $record->is_sworn : true) && 
                                        ($packAvailable ? $record->is_packed : true) && 
                                        ($glassAvailable ? $record->glass_acceptance === 'Готово' : true) && 
                                        $record->cut_status === 'Готово');
                            })
                    ),
                    
                Tables\Columns\TextColumn::make('total_price')
                    ->label(fn () => new HtmlString('Общая <br> Аванс <br> Остаток'))
                    ->wrapHeader()
                    ->money('RUB')
                    ->alignEnd()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListOrderJournals::route('/'),
            'create' => Pages\CreateOrderJournal::route('/create'),
            'edit' => Pages\EditOrderJournal::route('/{record}/edit'),
        ];
    }
}
