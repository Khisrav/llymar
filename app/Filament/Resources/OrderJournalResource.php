<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderJournalResource\Pages;
use App\Filament\Resources\OrderJournalResource\RelationManagers;
use App\Http\Controllers\SketchController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\LogisticsCompany;
use Filament\Actions\Action;
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
use Carbon\Carbon;

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

                // New is_sketch_sent column
                Tables\Columns\IconColumn::make('is_sketch_sent')
                    ->label(fn () => new HtmlString('Чертеж <br> отправлен'))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->is_sketch_sent) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        // Check if 24h has passed since order creation and status is paid
                        $hoursPassed = $record->created_at->diffInHours(now());
                        if ($hoursPassed >= 24 && $record->status === 'paid') {
                            return $hoursPassed >= 48 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-exclamation-circle';
                        }
                        
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->is_sketch_sent) {
                            return 'success';
                        }
                        
                        $hoursPassed = $record->created_at->diffInHours(now());
                        if ($hoursPassed >= 24 && $record->status === 'paid') {
                            return $hoursPassed >= 48 ? 'danger' : 'primary';
                        }
                        
                        return 'warning';
                    })
                    ->action(
                        Tables\Actions\Action::make('toggle_sketch_sent')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Подтверждение')
                            ->modalDescription('Вы уверены, что хотите отметить чертеж как отправленный?')
                            ->modalSubmitActionLabel('Да, отметить')
                            ->modalCancelActionLabel('Отмена')
                            ->action(function ($record) {
                                $record->update([
                                    'is_sketch_sent' => true,
                                    'sketched_at' => now()
                                ]);
                            })
                            ->disabled(fn ($record) => $record->is_sketch_sent)
                    ),
                    
                Tables\Columns\IconColumn::make('cut_status')
                    ->label('Распил')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->cut_status === 'Готово') {
                            return 'heroicon-o-check-circle';
                        }
                        
                        // if (!$record->is_sketch_sent) {
                        //     return 'heroicon-o-no-symbol';
                        // }
                        
                        // Check for milling (фрезеровка)
                        if ($record->orderItems->contains(fn ($item) => $item->item_id == 388)) {
                            return 'heroicon-o-scissors';
                        }
                        
                        // Check timing for urgency
                        if ($record->sketched_at) {
                            $hoursPassed = Carbon::parse($record->sketched_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-exclamation-circle';
                            }
                        }
                        
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->cut_status === 'Готово') {
                            return 'success';
                        }
                        
                        if (!$record->is_sketch_sent) {
                            return 'gray';
                        }
                        
                        if ($record->sketched_at) {
                            $hoursPassed = Carbon::parse($record->sketched_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'danger' : 'primary';
                            }
                        }

                        return 'warning';
                    })
                    ->action(
                        Tables\Actions\Action::make('update_cut_status')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->requiresConfirmation()
                            ->modalHeading('Обновить статус распила')
                            ->modalDescription('Выберите новый статус распила')
                            ->form([
                                Forms\Components\Select::make('cut_status')
                                    ->label('Статус распила')
                                    ->default(fn ($record) => $record->cut_status)
                                    ->options(function ($record) {
                                        $hasMillingItem = $record->orderItems->contains(fn ($item) => $item->item_id == 388);
                                        return [
                                            $hasMillingItem ? 'Фрезеровка' : 'Сборка' => $hasMillingItem ? 'Фрезеровка' : 'Сборка',
                                            'Готово' => 'Готово'
                                        ];
                                    })
                                    ->required()
                            ])
                            ->action(function ($record, array $data) {
                                $updates = ['cut_status' => $data['cut_status']];
                                if ($data['cut_status'] === 'Готово') {
                                    $updates['cut_at'] = now();
                                }
                                $record->update($updates);
                            })
                            ->disabled(fn ($record) => !$record->is_sketch_sent)
                    ),
                
                Tables\Columns\SelectColumn::make('glass_acceptance')
                    ->label('Прием стекла')
                    ->wrapHeader()
                    ->disabled(function ($record) {
                        // Disabled if sketch not sent OR if no glass items
                        return !$record->is_sketch_sent || !$record->getGlassCodeAttribute();
                    })
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state == 'Готово') {
                            $record->update(['glass_ready_at' => now()]);
                        } else if ($state == 'Рекламация') {
                            $record->update(['glass_complaint_at' => now()]);
                        } else if ($state == 'Переделка') {
                            $record->update(['glass_rework_at' => now()]);
                        }
                    })
                    ->options([
                        'Рекламация' => 'Рекламация',
                        'Переделка' => 'Переделка',
                        'Готово' => 'Готово',
                    ]),
                    
                Tables\Columns\IconColumn::make('is_painted')
                    ->label('Покраска')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->is_painted) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        // Check if painting is disabled
                        $hasPaintItems = $record->orderItems->contains(fn ($item) => in_array($item->item_id, [386, 435]));
                        if (!$hasPaintItems || $record->glass_acceptance !== 'Готово') {
                            return 'heroicon-o-no-symbol';
                        }
                        
                        // Check timing for urgency
                        if ($record->cut_at) {
                            $hoursPassed = Carbon::parse($record->cut_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-exclamation-circle';
                            }
                        }
                        
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->is_painted) {
                            return 'success';
                        }
                        
                        $hasPaintItems = $record->orderItems->contains(fn ($item) => in_array($item->item_id, [386, 435]));
                        if (!$hasPaintItems || $record->glass_acceptance !== 'Готово') {
                            return 'black';
                        }
                        
                        if ($record->cut_at) {
                            $hoursPassed = Carbon::parse($record->cut_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'danger' : 'primary';
                            }
                        }
                    
                        return 'warning';
                    })
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
                                $updates = ['is_painted' => !$record->is_painted];
                                if (!$record->is_painted) { // If setting to true
                                    $updates['painted_at'] = now();
                                }
                                $record->update($updates);
                            })
                            ->disabled(function ($record) {
                                $hasPaintItems = $record->orderItems->contains(fn ($item) => in_array($item->item_id, [386, 435]));
                                return !$hasPaintItems || $record->glass_acceptance !== 'Готово';
                            })
                    ),
                    
                Tables\Columns\IconColumn::make('is_sworn')
                    ->label('Поклейка')
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->is_sworn) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        // Check if sworn is disabled
                        $hasSwornItems = $record->orderItems->contains(fn ($item) => $item->item_id == 389);
                        if (!$hasSwornItems && $record->glass_acceptance !== 'Готово') {
                            return 'heroicon-o-no-symbol';
                        }
                        
                        // Check timing for urgency - based on painted_at or glass_ready_at
                        $checkDate = $record->painted_at ?: $record->glass_ready_at;
                        if ($checkDate) {
                            $hoursPassed = Carbon::parse($checkDate)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-exclamation-circle';
                            }
                        }
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->is_sworn) {
                            return 'success';
                        }
                        
                        $hasSwornItems = $record->orderItems->contains(fn ($item) => $item->item_id == 389);
                        if (!$hasSwornItems && $record->glass_acceptance !== 'Готово') {
                            return 'gray';
                        }
                        
                        $checkDate = $record->painted_at ?: $record->glass_ready_at;
                        if ($checkDate) {
                            $hoursPassed = Carbon::parse($checkDate)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'danger' : 'primary';
                            }
                        }
                    
                        return 'warning';
                    })
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
                                $updates = ['is_sworn' => !$record->is_sworn];
                                if (!$record->is_sworn) { // If setting to true
                                    $updates['sworn_at'] = now();
                                }
                                $record->update($updates);
                            })
                            ->disabled(function ($record) {
                                $hasSwornItems = $record->orderItems->contains(fn ($item) => $item->item_id == 389);
                                return !$hasSwornItems && $record->glass_acceptance !== 'Готово';
                            })
                    ),
                    
                Tables\Columns\IconColumn::make('is_packed')
                    ->label('Упаковка')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->is_packed) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        if (!$record->is_sworn) {
                            return 'heroicon-o-no-symbol';
                        }
                        
                        // Check timing for urgency
                        if ($record->sworn_at) {
                            $hoursPassed = Carbon::parse($record->sworn_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-exclamation-circle';
                            }
                        }
                    
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->is_packed) {
                            return 'success';
                        }
                        
                        if (!$record->is_sworn) {
                            return 'gray';
                        }
                        
                        if ($record->sworn_at) {
                            $hoursPassed = Carbon::parse($record->sworn_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'danger' : 'primary';
                            }
                        }
                    
                        return 'warning';
                    })
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
                                $updates = ['is_packed' => !$record->is_packed];
                                if (!$record->is_packed) { // If setting to true
                                    $updates['packed_at'] = now();
                                }
                                $record->update($updates);
                            })
                            ->disabled(fn ($record) => !$record->is_sworn)
                    ),
                    
                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Завершен')
                    ->alignCenter()
                    ->wrapHeader()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->is_completed) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        // if (!$record->is_packed) {
                        //     return 'heroicon-o-no-symbol';
                        // }
                        
                        // Check timing for urgency
                        if ($record->packed_at) {
                            $hoursPassed = Carbon::parse($record->packed_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-exclamation-circle';
                            }
                        }
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->is_completed) {
                            return 'success';
                        }
                        
                        if (!$record->is_packed) {
                            return 'gray';
                        }
                        
                        if ($record->packed_at) {
                            $hoursPassed = Carbon::parse($record->packed_at)->diffInHours(now());
                            if ($hoursPassed >= 48) {
                                return $hoursPassed >= 72 ? 'danger' : 'primary';
                            }
                        }
                    
                        return 'warning';
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
                                $updates = ['is_completed' => $newCompletedStatus];
                                if ($newCompletedStatus) {
                                    $updates['completed_at'] = now();
                                    
                                    // Update status based on order type
                                    if (str_starts_with($record->order_number, '1-')) {
                                        $updates['status'] = 'sent';
                                    } else if (str_starts_with($record->order_number, '4-') || str_starts_with($record->order_number, '6-')) {
                                        $updates['status'] = 'completed';
                                    }
                                }
                                
                                $record->update($updates);
                                
                                // Refresh the table to hide the completed order
                                if ($newCompletedStatus) {
                                    $livewire->dispatch('$refresh');
                                }
                            })
                            ->disabled(fn ($record) => !$record->is_packed)
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
