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
            //show only orders with status paid (i.e. not created or archived)
            ->modifyQueryUsing(function (Builder $query) {
                $query->with(['orderItems.item', 'orderOpenings.doorHandle']);
            })
            ->recordClasses(fn (Model $record): string => match ($record->id) {
                default => ' khisrav',
            })
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ | Дата')
                    ->formatStateUsing(function ($state, $record): string {
                        $number = explode('-', $state);
                        $color = $number[0] == '1' ? 'cyan' : ($number[0] == '4' ? 'gray' : 'success');
                        
                        $days = date_diff(date_create($record->when_started_working_on_it), date_create(now()));
                        $days_str = $days->days > 0 ? '| ' . $days->days . 'дн.' : '';
                        
                        return '<span style="background-color: ' . ($color == 'cyan' ? '#06b6d4' : ($color == 'gray' ? '#6b7280' : '#10b981')) . '; color: white; padding: 2px 6px; border-radius: 4px;">' . $state . '</span><br>' . $record->created_at->format('d.m.Y') . ' ' . $days_str;
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
                    
                Tables\Columns\SelectColumn::make('raspil')
                    ->label('Распил')
                    // ->native(false)
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->options([
                        'Сборка' => 'Сборка',
                        'Фрезеровка' => 'Фрезеровка',
                        'Готово' => 'Готово',
                    ]),
                    
                Tables\Columns\CheckboxColumn::make('pokraska')
                    ->label('Покраска')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->disabled(fn ($record) => !$record->ral_code),
                    
                Tables\Columns\SelectColumn::make('priem_stekla')
                    ->label('Прием стекла')
                    ->wrapHeader()
                    ->disabled(fn ($record) => !$record->getGlassCodeAttribute())
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->options([
                        'Рекламация' => 'Рекламация',
                        'Переделка' => 'Переделка',
                        'Готово' => 'Готово',
                    ]),
                    
                Tables\Columns\CheckboxColumn::make('pokleyka')
                    ->label('Поклейка')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter(),
                    
                Tables\Columns\CheckboxColumn::make('upakovka')
                    ->label('Упаковка')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('montazh')
                    ->label('Монтаж / Отправка')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Общая / Аванс / Остаток')
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
