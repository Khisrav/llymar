<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractTemplateResource\Pages;
use App\Filament\Resources\ContractTemplateResource\RelationManagers;
use App\Models\Contract;
use App\Models\ContractTemplate;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Split;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ContractTemplateResource extends Resource
{
    protected static ?string $model = ContractTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationLabel = 'Шаблоны договоров';
    protected static ?string $navigationGroup = 'Настройки';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                    Hidden::make('user_id')
                        ->default(fn () => auth()->id())
                        ->required(),

                    Grid::make()
                        ->columns(1)
                        ->schema([
                            TextInput::make('name')
                                ->label('Название шаблона')
                                ->required(),
                            Split::make([
                                Textarea::make('description')
                                    ->rows(3)
                                    ->label('Описание'),
                                FileUpload::make('attachment')
                                    ->label('Шаблон договора Word')
                                    ->downloadable()
                                    ->directory(fn () => 'contracts/' . auth()->id())
                                    ->storeFileNamesIn('attachment_original_filename')
                                    ->maxSize(1024 * 16) // 16MB
                                    ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->required(),
                            ])->from('md'),
                            Select::make('fields')
                                ->label('Переменные')
                                ->multiple()
                                ->options(Contract::getColumns())
                                ->required()
                                ->helperText(new HtmlString("
                                <div class='text-xs'>
                                    <div>Выбранные переменные должны пристуствовать в файле шаблона. <br><br></div>
                                    <table cellpadding='5'>
                                        <thead><tr><th><b>Переменная</b></th><th><b>Описание</b></th></tr></thead>
                                        <tbody>
                                            <tr><td><code>template_id</code></td><td>ID шаблона</td></tr>
                                            <tr><td><code>number</code></td><td>Номер договора</td></tr>
                                            <tr><td><code>date</code></td><td>Дата договора</td></tr>
                                            <tr><td><code>counterparty_fullname</code></td><td>ФИО заказчика/руководителя</td></tr>
                                            <tr><td><code>counterparty_type</code></td><td>Тип заказчика (ФЛ, ЮЛ, ИП)</td></tr>
                                            <tr><td><code>counterparty_phone</code></td><td>Телефон заказчика</td></tr>
                                            <tr><td><code>installation_address</code></td><td>Адрес установки</td></tr>
                                            <tr><td><code>price</code></td><td>Цена</td></tr>
                                            <tr><td><code>advance_payment_percentage</code></td><td>% аванса</td></tr>
                                            <tr><td><code>department_code</code></td><td>Код подразделения</td></tr>
                                            <tr><td><code>index</code></td><td>Индекс</td></tr>
                                        </tbody>
                                    </table>
                                </div>")),
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('description')
                    ->label('Описание')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Скачать')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (ContractTemplate $record) => Storage::url($record->attachment))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageContractTemplates::route('/'),
        ];
    }
}
