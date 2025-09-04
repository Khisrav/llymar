<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderJournalResource\Pages;
use App\Filament\Resources\OrderJournalResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class OrderJournalResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Журнал заказов';
    protected static ?string $navigationGroup = 'Заказы';

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
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Номер заказа')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer_address')
                    ->label('Адрес')
                    ->limit(50),
                Tables\Columns\TextColumn::make('status')
                    ->label('Статус')
                    ->badge(),
                //which glass is in the order
                Tables\Columns\TextColumn::make('glass_name')
                    ->label('Стекло')
                    ->formatStateUsing(function (Model $record) {
                        Log::info($record);
                        return $record->orderItems;
                    }),
                //door handles from order openings
                // Tables\Columns\TextColumn::make('door_handles')
                //     ->label('Ручки')
                //     ->formatStateUsing(function (Model $record) {
                //         try {
                //             $doorHandles = [];
                //             $orderOpenings = $record->orderOpenings;
                //             Log::info('DEBUG: Order ID: ' . $record->id . ', OrderOpenings count: ' . $orderOpenings->count());
                            
                //             if ($orderOpenings->count() === 0) {
                //                 Log::info('DEBUG: No order openings found for order ' . $record->id);
                //                 return 'Нет проемов';
                //             }
                            
                //             foreach ($orderOpenings as $opening) {
                //                 Log::info('DEBUG: Opening ID: ' . $opening->id . ', door_handle_item_id: ' . ($opening->door_handle_item_id ?? 'null'));
                                
                //                 if ($opening->door_handle_item_id) {
                //                     if ($opening->doorHandle) {
                //                         Log::info('DEBUG: Door handle found: ' . $opening->doorHandle->name);
                //                         $doorHandles[] = $opening->doorHandle->name;
                //                     } else {
                //                         Log::info('DEBUG: Door handle is null for door_handle_item_id: ' . $opening->door_handle_item_id);
                //                     }
                //                 }
                //             }
                            
                //             Log::info('DEBUG: Total door handles found: ' . count($doorHandles));
                //             return $doorHandles ? implode('<br>', array_unique($doorHandles)) : 'Ручки не найдены';
                //         } catch (\Exception $e) {
                //             Log::error('DEBUG: Error in door_handles: ' . $e->getMessage());
                //             return 'Ошибка: ' . $e->getMessage();
                //         }
                //     })
                //     ->html(),
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
            'index' => Pages\ListOrderJournals::route('/'),
            'create' => Pages\CreateOrderJournal::route('/create'),
            'edit' => Pages\EditOrderJournal::route('/{record}/edit'),
        ];
    }
}
