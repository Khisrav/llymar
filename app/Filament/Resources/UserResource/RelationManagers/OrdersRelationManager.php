<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\Order;
use App\Services\TochkaBankService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Заказы';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('user_id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('order_number')
                    ->label('№')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('customer_name')
                    ->label('ФИО клиента')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('customer_phone')
                    ->label('Телефон')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('total_price')
                    ->label('Цена')
                    ->money('RUB')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('status')
                    ->label('Статус')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'created' => 'info',
                        'paid' => 'primary',
                        'expired' => 'danger',
                        'assembled' => 'gray',
                        'sent' => 'warning',
                        'completed' => 'success',
                        'archived' => 'gray',
                        'unknown' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'created' => 'Создан',
                        'paid' => 'Оплачен',
                        'expired' => 'Просрочен',
                        'assembled' => 'Собран',
                        'sent' => 'Отправлен',
                        'completed' => 'Завершен',
                        'archived' => 'Архивирован',
                        'unknown' => 'Неизвестно',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'created' => 'Создан',
                        'paid' => 'Оплачен',
                        'expired' => 'Просрочен',
                        'assembled' => 'Собран',
                        'sent' => 'Отправлен',
                        'completed' => 'Выполнен',
                        'archived' => 'Архивирован',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('view')
                    ->label('Просмотр')
                    ->url(fn (Order $record) => route(auth()->user()->can('update order') ? 'filament.admin.resources.orders.edit' : 'filament.admin.resources.orders.view', $record->id))
                    // ->openUrlInNewTab()
                    ->icon('heroicon-o-eye'),
                    ActionGroup::make([
                        EditAction::make(),
                        Action::make('list_pdf')
                            ->label('Спецификация')
                            ->url(fn (Order $record) => route('orders.list_pdf', $record->id))
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-arrow-down-tray'),
                        Action::make('create_contract')
                            ->label('Создать Договор')
                            ->url(fn (Order $record) => route('filament.admin.resources.contracts.create', ['order_id' => $record->id, 'order_price' => $record->total_price]))
                            ->icon('heroicon-o-document-text')
                            ->visible(fn (Order $record) => $record->contracts()->count() === 0),
                        Action::make('view_contract')
                            ->label('Просмотр Договора')
                            ->url(fn (Order $record) => route('filament.admin.resources.contracts.edit', ['record' => $record->contracts()->first()->id]))
                            ->icon('heroicon-o-document-text')
                            ->visible(fn (Order $record) => $record->contracts()->count() > 0),
                        Action::make('create_bill')
                            ->label('Создать счет')
                            ->action(function (Order $record) {
                                try {
                                    $tochkaService = new TochkaBankService();
                                    $response = $tochkaService->createBill($record);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Успешно')
                                        ->body('Счет создан успешно')
                                        ->success()
                                        ->send();
                                        
                                    return redirect()->back();
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Ошибка')
                                        ->body('Не удалось создать счет: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->icon('heroicon-o-plus-circle')
                            ->visible(fn (Order $record) => empty($record->invoice_id)),
                        Action::make('check_payment_status')
                            ->label('Проверить оплату')
                            ->action(function (Order $record) {
                                try {
                                    $tochkaService = new TochkaBankService();
                                    $response = $tochkaService->getBillPaymentStatus($record);
                                    
                                    $paymentStatuses = [
                                        'payment_waiting' => 'Ожидание оплаты',
                                        'payment_expired' => 'Просрочен',
                                        'payment_paid' => 'Оплачен',
                                    ];
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Статус оплаты')
                                        ->body('Статус: ' . ($paymentStatuses[$response['Data']['paymentStatus']] ?? 'неизвестен'))
                                        ->info()
                                        ->send();
                                        
                                    return redirect()->back();
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Ошибка')
                                        ->body('Не удалось проверить статус: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->icon('heroicon-o-credit-card')
                            ->visible(fn (Order $record) => !empty($record->invoice_id)),
                        Action::make('invoice_pdf')
                            ->label('Скачать счет PDF')
                            ->url(function (Order $record) {
                                return route('orders.download_bill', ['order' => $record->id]);
                            })
                            ->openUrlInNewTab()
                            ->icon('heroicon-o-document-arrow-down')
                            ->visible(fn (Order $record) => !empty($record->invoice_id)),
                        Action::make('send_bill_email')
                            ->label('Отправить на email')
                            ->form([
                                Forms\Components\TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->default(fn (Order $record) => $record->customer_email)
                            ])
                            ->action(function (Order $record, array $data) {
                                try {
                                    $tochkaService = new TochkaBankService();
                                    $response = $tochkaService->sendBillToEmail($record, $data['email']);
                                    
                                    \Filament\Notifications\Notification::make()
                                        ->title('Успешно')
                                        ->body('Счет отправлен на email')
                                        ->success()
                                        ->send();
                                } catch (\Exception $e) {
                                    \Filament\Notifications\Notification::make()
                                        ->title('Ошибка')
                                        ->body('Не удалось отправить счет: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            })
                            ->icon('heroicon-o-envelope')
                            ->visible(fn (Order $record) => !empty($record->invoice_id)),
                    ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
