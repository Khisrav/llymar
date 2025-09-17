<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderJournalStatisticsResource\Pages;
use App\Filament\Resources\OrderJournalStatisticsResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;
use Carbon\Carbon;

class OrderJournalStatisticsResource extends Resource
{
    protected static ?string $model = Order::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function getNavigationIcon(): ?string
    {
        return null;
    }

    public static function getModelLabel(): string
    {
        return 'Статистика журнала заказов';
    }

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
                $query->where('status', 'paid');
            })
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('№ Заказа')
                    ->formatStateUsing(function ($state): HtmlString {
                        return new HtmlString('<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">' . $state . '</span>');
                    })
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sketched_at')
                    ->label('Чертеж')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('cut_at')
                    ->label('Распил')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('painted_at')
                    ->label('Покраска')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('packed_at')
                    ->label('Упаковка')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('sworn_at')
                    ->label('Поклейка')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('glass_rework_at')
                    ->label('Переделка стекла')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('glass_complaint_at')
                    ->label('Рекламация стекла')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('glass_ready_at')
                    ->label('Готовность стекла')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Завершен')
                    ->formatStateUsing(function ($state, $record): HtmlString {
                        if (!$state) {
                            return new HtmlString('<span class="text-gray-400">—</span>');
                        }
                        $date = Carbon::parse($state);
                        $hoursAgo = $date->diffInHours(now());
                        return new HtmlString('<span title="' . $date->format('d.m.Y H:i') . '">' . $hoursAgo . 'ч назад</span>');
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Removed edit action
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
            'index' => Pages\ListOrderJournalStatistics::route('/'),
            'create' => Pages\CreateOrderJournalStatistics::route('/create'),
            'edit' => Pages\EditOrderJournalStatistics::route('/{record}/edit'),
        ];
    }
}
