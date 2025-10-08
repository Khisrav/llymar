<?php

namespace App\Filament\Resources\RegistrationLinkResource\Pages;

use App\Filament\Resources\RegistrationLinkResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Auth;

class ManageRegistrationLinks extends ManageRecords
{
    protected static string $resource = RegistrationLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Создать ссылку')
                ->icon('heroicon-o-plus')
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = Auth::id();
                    return $data;
                })
                ->successNotification(function ($record) {
                    return Notification::make()
                        ->success()
                        ->title('Ссылка создана')
                        ->body("Ссылка действительна до {$record->expires_at->format('d.m.Y H:i')}")
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('copy')
                                ->label('Копировать ссылку')
                                ->button()
                                ->close()
                                ->extraAttributes([
                                    'x-on:click' => 'navigator.clipboard.writeText("' . $record->url . '")',
                                ]),
                        ])
                        ->persistent();
                }),
        ];
    }
}
