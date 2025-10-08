<?php

namespace Database\Seeders;

use App\Models\LandingPageOption;
use Illuminate\Database\Seeder;

class LandingPageOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            // General Settings
            [
                'key' => 'site_name',
                'label' => 'Название сайта',
                'value' => 'LLYMAR',
                'type' => 'text',
                'description' => 'Основное название сайта',
                'group' => 'general',
                'order' => 1,
            ],
            [
                'key' => 'site_tagline',
                'label' => 'Слоган',
                'value' => 'Ваш комфорт - наша работа!',
                'type' => 'text',
                'description' => 'Краткий слоган компании',
                'group' => 'general',
                'order' => 2,
            ],
            [
                'key' => 'hero_title',
                'label' => 'Заголовок главной страницы',
                'value' => 'Премиальное безрамное остекление',
                'type' => 'text',
                'description' => 'Основной заголовок на главной странице',
                'group' => 'general',
                'order' => 3,
            ],
            [
                'key' => 'hero_subtitle',
                'label' => 'Подзаголовок главной страницы',
                'value' => 'Превратите свое пространство в произведение искусства с нашими безрамными системами остекления.',
                'type' => 'textarea',
                'description' => 'Описание под заголовком на главной странице',
                'group' => 'general',
                'order' => 4,
            ],

            // SEO Settings
            [
                'key' => 'meta_title',
                'label' => 'SEO заголовок',
                'value' => 'Безрамное остекление в Краснодаре',
                'type' => 'text',
                'description' => 'Заголовок для поисковых систем',
                'group' => 'seo',
                'order' => 1,
            ],
            [
                'key' => 'meta_description',
                'label' => 'SEO описание',
                'value' => 'Безрамное раздвижное остекление террас, веранд, беседок, кафе и ресторанов. Европейское качество, установка за 1-3 дня.',
                'type' => 'textarea',
                'description' => 'Описание для поисковых систем',
                'group' => 'seo',
                'order' => 2,
            ],
            [
                'key' => 'meta_keywords',
                'label' => 'Ключевые слова',
                'value' => 'безрамное остекление, остекление террас, остекление веранд, остекление беседок, остекление кафе, остекление ресторанов',
                'type' => 'textarea',
                'description' => 'Ключевые слова для SEO (через запятую)',
                'group' => 'seo',
                'order' => 3,
            ],
            [
                'key' => 'og_image',
                'label' => 'Изображение для соцсетей',
                'value' => '/assets/hero.jpg',
                'type' => 'text',
                'description' => 'URL изображения для Open Graph (соцсети)',
                'group' => 'seo',
                'order' => 4,
            ],

            // Contact Information
            [
                'key' => 'phone',
                'label' => 'Номер телефона',
                'value' => '+7 989 804 12-34',
                'type' => 'tel',
                'description' => 'Основной номер телефона',
                'group' => 'contact',
                'order' => 1,
            ],
            [
                'key' => 'phone_formatted',
                'label' => 'Телефон (для отображения)',
                'value' => '+7 (989) 804 12-34',
                'type' => 'text',
                'description' => 'Отформатированный номер телефона для отображения',
                'group' => 'contact',
                'order' => 2,
            ],
            [
                'key' => 'email',
                'label' => 'Email',
                'value' => 'info@llymar.ru',
                'type' => 'email',
                'description' => 'Контактный email',
                'group' => 'contact',
                'order' => 3,
            ],
            [
                'key' => 'address',
                'label' => 'Адрес',
                'value' => 'г. Краснодар, ул. Уральская, 145/3',
                'type' => 'text',
                'description' => 'Физический адрес компании',
                'group' => 'contact',
                'order' => 4,
            ],
            [
                'key' => 'address_city',
                'label' => 'Город',
                'value' => 'Краснодар',
                'type' => 'text',
                'description' => 'Город',
                'group' => 'contact',
                'order' => 5,
            ],
            [
                'key' => 'address_region',
                'label' => 'Регион',
                'value' => 'Краснодарский край',
                'type' => 'text',
                'description' => 'Регион/Область',
                'group' => 'contact',
                'order' => 6,
            ],
            [
                'key' => 'postal_code',
                'label' => 'Почтовый индекс',
                'value' => '350080',
                'type' => 'text',
                'description' => 'Почтовый индекс',
                'group' => 'contact',
                'order' => 7,
            ],
            [
                'key' => 'geo_latitude',
                'label' => 'Широта',
                'value' => '45.044534',
                'type' => 'text',
                'description' => 'Географическая широта (для карт)',
                'group' => 'contact',
                'order' => 8,
            ],
            [
                'key' => 'geo_longitude',
                'label' => 'Долгота',
                'value' => '39.114309',
                'type' => 'text',
                'description' => 'Географическая долгота (для карт)',
                'group' => 'contact',
                'order' => 9,
            ],

            // Business Information
            [
                'key' => 'working_hours',
                'label' => 'Часы работы',
                'value' => 'Пн-Пт: 09:00-20:00',
                'type' => 'text',
                'description' => 'Рабочие часы компании',
                'group' => 'business',
                'order' => 1,
            ],
            [
                'key' => 'founding_year',
                'label' => 'Год основания',
                'value' => '2019',
                'type' => 'text',
                'description' => 'Год основания компании',
                'group' => 'business',
                'order' => 2,
            ],
            [
                'key' => 'employees_count',
                'label' => 'Количество сотрудников',
                'value' => '10-50',
                'type' => 'text',
                'description' => 'Примерное количество сотрудников',
                'group' => 'business',
                'order' => 3,
            ],

            // Social Media
            [
                'key' => 'social_facebook',
                'label' => 'Facebook',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на страницу в Facebook',
                'group' => 'social',
                'order' => 1,
            ],
            [
                'key' => 'social_instagram',
                'label' => 'Instagram',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на страницу в Instagram',
                'group' => 'social',
                'order' => 2,
            ],
            [
                'key' => 'social_vk',
                'label' => 'ВКонтакте',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на страницу ВКонтакте',
                'group' => 'social',
                'order' => 3,
            ],
            [
                'key' => 'social_youtube',
                'label' => 'YouTube',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на канал YouTube',
                'group' => 'social',
                'order' => 4,
            ],
            [
                'key' => 'yandex_maps_link',
                'label' => 'Яндекс.Карты',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на организацию в Яндекс.Картах',
                'group' => 'social',
                'order' => 5,
            ],
            [
                'key' => 'social_rutube',
                'label' => 'RuTube',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на канал в RuTube',
                'group' => 'social',
                'order' => 6,
            ],
            [
                'key' => 'social_dzen',
                'label' => 'Яндекс.Дзен',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на канал в Яндекс.Дзен',
                'group' => 'social',
                'order' => 7,
            ],
            [
                'key' => 'social_telegram',
                'label' => 'Telegram',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на канал/бот в Telegram',
                'group' => 'social',
                'order' => 8,
            ],
            [
                'key' => 'social_odnoklassniki',
                'label' => 'Одноклассники',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на страницу в Одноклассниках',
                'group' => 'social',
                'order' => 9,
            ],
            [
                'key' => 'social_max_messenger',
                'label' => 'Max Messenger',
                'value' => '',
                'type' => 'url',
                'description' => 'Ссылка на профиль в Max Messenger',
                'group' => 'social',
                'order' => 10,
            ],
        ];

        foreach ($options as $option) {
            LandingPageOption::updateOrCreate(
                ['key' => $option['key']],
                $option
            );
        }
    }
}

