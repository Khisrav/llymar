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
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\ActionGroup;

class OrderJournalResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'ЦЕХ';
    protected static ?string $pluralModelLabel = 'ЦЕХ';
    // protected static ?string $navigationGroup = 'Заказы';
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'paid')->count();
    }
    
    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::where('status', 'paid')->count();
        
        if ($count === 0) {
            return 'success';
        } elseif ($count <= 5) {
            return 'warning';
        } else {
            return 'danger';
        }
    }
    
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
            ->recordAction('view_order_details')
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
                    ->suffix(fn (?Model $record): HtmlString => new HtmlString(
                        '<br>' . SketchController::calculateGlassArea($record->id)['total_area_m2'] . 'м²'
                    ))
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('xs')
                    ->html(),

                Tables\Columns\TextColumn::make('handles')
                    ->label('Ручки')
                    ->html()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size('xs'),

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
                    
                Tables\Columns\IconColumn::make('cut_status_display')
                    ->label('Распил')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->alignCenter()
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->state(function ($record) {
                        // Always return a non-null value so icon shows
                        return 'active';
                    })
                    ->icon(function ($record) {
                        // Check for milling (фрезеровка)
                        $hasMillingItem = $record->orderItems->contains(fn ($item) => $item->item_id == 388);
                        
                        if ($record->cut_status) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        if (!$hasMillingItem) {
                            return 'heroicon-o-no-symbol';
                        }
                        
                        if ($hasMillingItem) {
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
                        if ($record->cut_status) {
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
                            ->action(function ($record) {
                                $record->update([
                                    'cut_status' => true,
                                    'cut_at' => now()
                                ]);
                            })
                            ->disabled(fn ($record) => !$record->is_sketch_sent)
                    ),
                
                Tables\Columns\IconColumn::make('glass_status_display')
                    ->label('Прием стекла')
                    ->wrapHeader()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->state(function ($record) {
                        // Always return a non-null value so icon shows
                        return 'active';
                    })
                    ->icon(function ($record) {
                        if ($record->glass_acceptance == 'Готово') {
                            return 'heroicon-o-check-circle';
                        }
                        
                        if ($record->glass_acceptance != 'Готово' && $record->glass_acceptance != null) {
                            return 'heroicon-o-arrow-path';
                        }
                        
                        // Disabled if sketch not sent OR if no glass items
                        if (!$record->getGlassCodeAttribute()) {
                            return 'heroicon-o-no-symbol';
                        }
                        
                        return 'heroicon-o-bell';
                    })
                    ->color(function ($record) {
                        if ($record->glass_acceptance == 'Готово') {
                            return 'success';
                        }
                        
                        if ($record->glass_acceptance == 'Рекламация') {
                            return 'danger';
                        }
                        
                        if ($record->glass_acceptance == 'Переделка') {
                            return 'warning';
                        }
                        
                        // Disabled if sketch not sent OR if no glass items
                        if (!$record->is_sketch_sent || !$record->getGlassCodeAttribute()) {
                            return 'gray';
                        }
                        
                        return 'warning';
                    })
                    ->action(
                        Tables\Actions\Action::make('update_glass_acceptance')
                            ->label('')
                            ->icon('')
                            ->color('primary')
                            ->form([
                                Forms\Components\Select::make('glass_acceptance')
                                    ->label('Статус приема стекла')
                                    ->options([
                                        'Рекламация' => 'Рекламация',
                                        'Переделка' => 'Переделка',
                                        'Готово' => 'Готово',
                                    ])
                                    ->default(fn ($record) => $record->glass_acceptance)
                                    ->required(),
                            ])
                            ->modalHeading('Обновить статус приема стекла')
                            ->modalDescription('Выберите новый статус приема стекла')
                            ->action(function ($record, array $data) {
                                $updates = ['glass_acceptance' => $data['glass_acceptance']];
                                
                                if ($data['glass_acceptance'] == 'Готово') {
                                    $updates['glass_ready_at'] = now();
                                } else if ($data['glass_acceptance'] == 'Рекламация') {
                                    $updates['glass_complaint_at'] = now();
                                } else if ($data['glass_acceptance'] == 'Переделка') {
                                    $updates['glass_rework_at'] = now();
                                }
                                
                                $record->update($updates);
                            })
                            ->disabled(function ($record) {
                                // Disabled if sketch not sent OR if no glass items
                                return !$record->is_sketch_sent || !$record->getGlassCodeAttribute();
                            })
                    ),
                    
                Tables\Columns\IconColumn::make('is_painted')
                    ->label('Покраска')
                    ->tooltip(fn ($record) => $record->ral_code)
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->size(IconColumnSize::TwoExtraLarge)
                    ->icon(function ($record) {
                        if ($record->is_painted) {
                            return 'heroicon-o-check-circle';
                        }
                        
                        // Check if painting is disabled
                        $hasPaintItems = $record->orderItems->contains(fn ($item) => in_array($item->item_id, [386, 435]));
                        if (!$hasPaintItems) {
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
                        
                        $cut_at = $record->cut_at;
                        $hasPaintItems = $record->orderItems->contains(fn ($item) => in_array($item->item_id, [386, 435]));
                        if (!$hasPaintItems || !$cut_at) {
                            return 'gray';
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
                        if (!$hasSwornItems || $record->glass_acceptance !== 'Готово') {
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
                                    
                                    // Show success notification
                                    Notification::make()
                                        ->title('Заказ успешно завершен')
                                        ->body("Заказ {$record->order_number} успешно завершен")
                                        ->success()
                                        ->send();
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
            ->defaultSort('when_started_working_on_it', 'desc')
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make('view_order_details')
                        ->label('Сводка')
                        ->icon('heroicon-o-eye')
                        // ->color('primary')
                        ->modalHeading(fn ($record) => 'Детали заказа ' . $record->order_number)
                        ->infolist(fn ($record) => static::orderInfolist($record))
                        ->modalWidth('7xl')
                        ->slideOver(),
                    //open order editing (only for admins, operators, workmans and rops)
                    Tables\Actions\Action::make('open_order')
                        ->label('К заказу')
                        ->icon('heroicon-o-arrow-right')
                        // ->color('primary')
                        ->url(fn ($record) => route('filament.admin.resources.orders.edit', $record->id))
                        // ->visible(fn ($record) => $record->status === 'sent')
                ])
                ->label('')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('primary')
                // ->size(ActionSize::ExtraSmall)
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function orderInfolist($record): Infolist
    {
        return Infolist::make()
            ->record($record)
            ->schema([
                Infolists\Components\Section::make('Информация о заказе')
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number')
                            ->label('Номер заказа')
                            ->badge()
                            ->color('primary'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Статус')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'paid' => 'Оплачен',
                                'created' => 'Создан',
                                'sent' => 'Отправлен',
                                'completed' => 'Завершен',
                                default => ucfirst($state)
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'completed' => 'success',
                                'sent' => 'warning',
                                default => 'gray'
                            }),
                        Infolists\Components\TextEntry::make('total_price')
                            ->label('Общая стоимость')
                            ->money('RUB')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large)
                            ->weight('bold'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Дата создания')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->compact()
                    ->collapsible()
                    ->columns([
                        'sm' => 2,
                        'md' => 4,
                    ]),

                Infolists\Components\Section::make('Информация о клиенте')
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_name')
                            ->label('Имя клиента')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('customer_email')
                            ->label('Email')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('customer_phone')
                            ->label('Телефон')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('customer_address')
                            ->label('Адрес')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('city')
                            ->label('Город')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('ral_code')
                            ->label('RAL код')
                            ->placeholder('—'),
                    ])
                    ->compact()
                    ->collapsible()
                    ->columns([
                        'sm' => 2,
                        'md' => 3,
                    ]),

                Infolists\Components\Section::make('Статус производства')
                    ->schema([
                        Infolists\Components\IconEntry::make('is_sketch_sent')
                            ->label('Чертеж отправлен')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        Infolists\Components\IconEntry::make('cut_status')
                            ->label('Распил выполнен')
                            ->boolean()
                            ->getStateUsing(fn ($record) => !empty($record->cut_status))
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        Infolists\Components\IconEntry::make('is_painted')
                            ->label('Покраска выполнена')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        Infolists\Components\IconEntry::make('is_completed')
                            ->label('Заказ завершен')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                        Infolists\Components\TextEntry::make('sketched_at')
                            ->label('Чертеж отправлен')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('cut_at')
                            ->label('Распил выполнен')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('painted_at')
                            ->label('Покраска выполнена')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('completed_at')
                            ->label('Заказ завершен')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                    ])
                    ->compact()
                    ->collapsible()
                    ->columns([
                        'sm' => 2,
                        'md' => 4,
                    ]),

                Infolists\Components\Section::make('Информация о стекле')
                    ->schema([
                        Infolists\Components\TextEntry::make('glass_code')
                            ->label('Код стекла')
                            ->getStateUsing(fn ($record) => $record->getGlassCodeAttribute())
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('glass_area')
                            ->label('Площадь стекла')
                            ->getStateUsing(function ($record) {
                                $area = \App\Http\Controllers\SketchController::calculateGlassArea($record->id);
                                return ($area['total_area_m2'] ?? '0') . ' м²';
                            }),
                        Infolists\Components\TextEntry::make('glass_acceptance')
                            ->label('Статус приема стекла')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'Готово' => 'success',
                                'Рекламация' => 'danger',
                                'Переделка' => 'warning',
                                default => 'gray'
                            })
                            ->placeholder('Ожидает'),
                    ])
                    ->compact()
                    ->columns([
                        'sm' => 2,
                        'md' => 3,
                    ])
                    ->collapsible()
                    ->visible(fn ($record) => $record->getGlassCodeAttribute() !== '—'),

                Infolists\Components\Section::make('Дополнительная информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('when_started_working_on_it')
                            ->label('Дата начала работы')
                            ->dateTime('d.m.Y H:i')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('readiness_date')
                            ->label('Дата готовности')
                            ->date('d.m.Y')
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('comment')
                            ->label('Комментарий')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        Infolists\Components\TextEntry::make('technical_comment')
                            ->label('Технический комментарий')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->compact()
                    ->columns([
                        'sm' => 2,
                        'md' => 2,
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrderJournals::route('/'),
            // 'create' => Pages\CreateOrderJournal::route('/create'),
            // 'edit' => Pages\EditOrderJournal::route('/{record}/edit'),
        ];
    }
}
