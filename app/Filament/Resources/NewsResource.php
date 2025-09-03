<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsResource\Pages;
use App\Models\News;
use App\Models\User;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup as ActionsActionGroup;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $navigationIcon = 'heroicon-o-newspaper';
    
    protected static ?string $navigationLabel = 'Новости';
    protected static ?string $modelLabel = 'Новость';
    protected static ?string $pluralModelLabel = 'Новости';
    protected static ?string $navigationGroup = 'Настройки';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('News Content')
                    ->tabs([
                        Tabs\Tab::make('Основное содержание')
                            ->schema([
                                Section::make('Основная информация')
                                    ->schema([
                                        TextInput::make('title')
                                            ->label('Заголовок')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                                if ($operation !== 'create') {
                                                    return;
                                                }
                                                $set('slug', News::generateSlug($state));
                                            }),

                                        TextInput::make('slug')
                                            ->label('URL слаг')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique(News::class, 'slug', ignoreRecord: true)
                                            ->rules(['alpha_dash'])
                                            ->helperText('Используется в URL. Должен содержать только буквы, цифры, дефисы и подчеркивания.'),

                                        FileUpload::make('cover_image')
                                            ->label('Обложка')
                                            ->image()
                                            ->directory('news/covers')
                                            ->disk('public')
                                            ->imageEditor()
                                            ->imageResizeMode('cover')
                                            ->imageCropAspectRatio('16:9')
                                            ->imageResizeTargetWidth('800')
                                            ->imageResizeTargetHeight('450')
                                            ->helperText('Рекомендуемый размер: 800x450px. Если не загружена, будет использовано случайное изображение.'),

                                        Textarea::make('excerpt')
                                            ->label('Краткое описание')
                                            ->rows(3)
                                            ->maxLength(500)
                                            ->helperText('Краткое описание статьи. Если не заполнено, будет сгенерировано автоматически из содержания.'),

                                        RichEditor::make('content')
                                            ->label('Содержание')
                                            ->required()
                                            ->columnSpanFull()
                                            ->fileAttachmentsDisk('public')
                                            ->fileAttachmentsDirectory('news/attachments')
                                            ->toolbarButtons([
                                                'attachFiles',
                                                'blockquote',
                                                'bold',
                                                'bulletList',
                                                'codeBlock',
                                                'h2',
                                                'h3',
                                                'italic',
                                                'link',
                                                'orderedList',
                                                'redo',
                                                'strike',
                                                'table',
                                                'undo',
                                            ]),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('SEO и публикация')
                            ->schema([
                                Section::make('SEO настройки')
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->label('SEO заголовок')
                                            ->maxLength(60)
                                            ->helperText('Оптимальная длина: 50-60 символов. Если не заполнено, используется основной заголовок.'),

                                        Textarea::make('seo_description')
                                            ->label('SEO описание')
                                            ->rows(3)
                                            ->maxLength(160)
                                            ->helperText('Оптимальная длина: 150-160 символов.'),
                                    ]),

                                Section::make('Публикация')
                                    ->schema([
                                        Select::make('author_id')
                                            ->label('Автор')
                                            ->relationship('author', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->default(Auth::id())
                                            ->required(),

                                        Select::make('status')
                                            ->label('Статус')
                                            ->options([
                                                'draft' => 'Черновик',
                                                'published' => 'Опубликовано',
                                                'archived' => 'В архиве',
                                            ])
                                            ->default('draft')
                                            ->required()
                                            ->live(),

                                        DateTimePicker::make('published_at')
                                            ->label('Дата публикации')
                                            ->helperText('Оставьте пустым для автоматической установки при публикации.')
                                            ->visible(fn (Forms\Get $get): bool => $get('status') === 'published'),
                                    ])
                                    ->columns(2),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('cover_image')
                    ->label('Обложка')
                    ->disk('public')
                    ->defaultImageUrl(fn ($record) => $record?->cover_image_url)
                    ->size(60)
                    ->square(),

                TextColumn::make('title')
                    ->label('Заголовок')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                TextColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'archived' => 'В архиве',
                    })
                    ->badge()
                    ->colors([
                        'warning' => 'draft',
                        'success' => 'published',
                        'secondary' => 'archived',
                    ]),

                TextColumn::make('author.name')
                    ->label('Автор')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('views')
                    ->label('Просмотры')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('published_at')
                    ->label('Дата публикации')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('Создано')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        'draft' => 'Черновик',
                        'published' => 'Опубликовано',
                        'archived' => 'В архиве',
                    ]),

                SelectFilter::make('author')
                    ->label('Автор')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionsActionGroup::make([
                    Tables\Actions\ViewAction::make()->url(fn (News $record): string => route('news.show', $record)),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNews::route('/'),
            'create' => Pages\CreateNews::route('/create'),
            'edit' => Pages\EditNews::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['author']);
    }
} 