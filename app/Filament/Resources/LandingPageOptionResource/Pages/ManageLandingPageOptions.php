<?php

namespace App\Filament\Resources\LandingPageOptionResource\Pages;

use App\Filament\Resources\LandingPageOptionResource;
use App\Models\LandingPageOption;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class ManageLandingPageOptions extends Page
{
    protected static string $resource = LandingPageOptionResource::class;

    protected static string $view = 'filament.pages.manage-landing-page-options';
    
    protected static ?string $title = 'Настройки главной страницы';

    public array $data = [];

    public function mount(): void
    {
        // Load all options and populate data array
        $options = LandingPageOption::orderBy('group')->orderBy('order')->get();
        
        foreach ($options as $option) {
            $this->data["option_{$option->id}"] = $option->value;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data');
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Settings')
                ->tabs([
                    Tabs\Tab::make('Общие')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Section::make('Основные настройки сайта')
                                ->description('Основная информация о сайте')
                                ->schema($this->getFieldsByGroup('general'))
                                ->columns(2),
                        ]),
                    
                    Tabs\Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make('Настройки SEO')
                                ->description('Оптимизация для поисковых систем')
                                ->schema($this->getFieldsByGroup('seo'))
                                ->columns(2),
                        ]),
                    
                    Tabs\Tab::make('Контакты')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            Section::make('Контактная информация')
                                ->description('Контактные данные компании')
                                ->schema($this->getFieldsByGroup('contact'))
                                ->columns(2),
                        ]),
                    
                    Tabs\Tab::make('О компании')
                        ->icon('heroicon-o-building-office')
                        ->schema([
                            Section::make('Информация о компании')
                                ->description('Общая информация о бизнесе')
                                ->schema($this->getFieldsByGroup('business'))
                                ->columns(2),
                        ]),
                    
                    Tabs\Tab::make('Соцсети')
                        ->icon('heroicon-o-share')
                        ->schema([
                            Section::make('Социальные сети')
                                ->description('Ссылки на профили в социальных сетях')
                                ->schema($this->getFieldsByGroup('social'))
                                ->columns(1),
                        ]),

                    Tabs\Tab::make('Калькулятор')
                        ->icon('heroicon-o-calculator')
                        ->schema([
                            Section::make('Пороги цен Р3 / Р2 / Р1')
                                ->description('Сумма состава системы по Р3. Ниже порога Р2 — Р3, от Р2 до Р1 — Р2, от Р1 и выше — Р1.')
                                ->schema($this->getFieldsByGroup('calculator'))
                                ->columns(2),
                        ]),
                ])
                ->columnSpanFull()
                ->persistTabInQueryString(),
        ];
    }

    protected function getFieldsByGroup(string $group): array
    {
        $options = LandingPageOption::where('group', $group)
            ->orderBy('order')
            ->get();

        $fields = [];

        foreach ($options as $option) {
            $field = match ($option->type) {
                'textarea' => Textarea::make("option_{$option->id}")
                    ->rows(3)
                    ->columnSpanFull(),
                'email' => TextInput::make("option_{$option->id}")
                    ->email(),
                'tel' => TextInput::make("option_{$option->id}")
                    ->tel(),
                'url' => TextInput::make("option_{$option->id}")
                    ->url(),
                'number' => TextInput::make("option_{$option->id}")
                    ->numeric()
                    ->minValue(0)
                    ->step(1)
                    ->suffix('₽'),
                default => TextInput::make("option_{$option->id}"),
            };

            $field = $field
                ->label($option->label)
                ->helperText($option->description);

            $fields[] = $field;
        }

        return $fields;
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Сохранить изменения')
                ->action('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $p2Option = LandingPageOption::where('key', 'factor_p2_from')->first();
        $p1Option = LandingPageOption::where('key', 'factor_p1_from')->first();

        if ($p2Option && $p1Option) {
            $p2 = (float) ($data["option_{$p2Option->id}"] ?? 0);
            $p1 = (float) ($data["option_{$p1Option->id}"] ?? 0);

            if ($p1 <= $p2) {
                Notification::make()
                    ->title('Ошибка')
                    ->danger()
                    ->body('Порог Р1 должен быть больше порога Р2.')
                    ->send();

                return;
            }
        }

        try {
            DB::beginTransaction();

            foreach ($data as $key => $value) {
                if (str_starts_with($key, 'option_')) {
                    $optionId = (int) str_replace('option_', '', $key);
                    LandingPageOption::where('id', $optionId)->update(['value' => $value]);
                }
            }

            DB::commit();

            Notification::make()
                ->title('Успешно сохранено')
                ->success()
                ->body('Настройки главной страницы обновлены.')
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Ошибка')
                ->danger()
                ->body('Не удалось сохранить настройки: ' . $e->getMessage())
                ->send();
        }
    }
}
