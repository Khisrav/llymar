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
                $query->with(['orderItems.item', 'orderOpenings.doorHandle']);
            })
            ->columns([
                Split::make([
                    Stack::make([
                        Tables\Columns\TextColumn::make('order_number')
                            ->badge()
                            ->color(function ($state): string {
                                $number = explode('-', $state);
                                if ($number[0] == '1') return 'cyan';
                                else if ($number[0] == '4') return 'gray';
                                else return 'success';
                            }),
                            
                        Tables\Columns\TextColumn::make('created_at')
                            ->dateTime('d.m.Y')
                            ->suffix(function ($record) {
                                if ($record->when_started_working_on_it) {
                                    $days = date_diff(date_create($record->when_started_working_on_it), date_create(now()));
                                    return ' (' . $days->days . 'дн.)';
                                }
                                return '';
                            })
                            ->tooltip(fn ($state): string => $state->format('d M Y H:i')),
                    ]),
                    
                    Stack::make([
                        Tables\Columns\TextColumn::make('customer_address')
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
                                } else return '';
                            })
                            ->limit(50),
                        
                        Tables\Columns\TextColumn::make('city')
                            ->searchable()
                            ->wrap()
                            ->limit(50),
                    ]),
                    
                    Tables\Columns\TextColumn::make('glass_code')
                        ->searchable()
                        ->suffix(fn (?Model $record): HtmlString => new HtmlString(
                            '<br>' . SketchController::calculateGlassArea($record->id)['total_area_m2'] . ' м²'
                        ))
                        ->html()
                        // ->size('xs')
                        ->wrap(),

                    //door handles from order openings
                    Tables\Columns\TextColumn::make('handles')
                        ->label('Ручки')
                        ->searchable()
                        ->size('xs')
                        ->html()
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                    Tables\Columns\TextColumn::make('raspil')
                        ->label('Распил')
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                    Tables\Columns\TextColumn::make('ral_code')
                        ->label('RAL')
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->badge()
                        ->color('gray')
                        ->wrap(),
                    Tables\Columns\TextColumn::make('priem_stekla')
                        ->label('Прием стекла')
                        ->wrapHeader()
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                    Tables\Columns\TextColumn::make('pokleyka')
                        ->label('Поклейка')
                        ->wrapHeader()
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                    Tables\Columns\TextColumn::make('upakovka')
                        ->label('Упаковка')
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                    Tables\Columns\TextColumn::make('montazh')
                        ->label('Мотнаж \ Отправка')
                        ->wrapHeader()
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                    Tables\Columns\TextColumn::make('total_price')
                        ->label('Общая \ Аванс \ Остаток')
                        ->wrapHeader()
                        ->money('RUB')
                        ->toggleable(isToggledHiddenByDefault: false)
                        ->wrap(),
                ]),
                
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
