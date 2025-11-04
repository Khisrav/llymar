<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TranslatePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vocabulary = [
            'AuthProvider' => '',
            'Category' => 'Категории',
            'CommercialOffer' => 'КП',
            'CommissionCredit' => 'Комисс. операции',
            'Company' => 'Компании',
            'CompanyBill' => 'Счета компании',
            'Contract' => 'Контракта',
            'ContractTemplate' => 'Шаблона договора',
            'HeroCarousel' => 'Карусели',
            'Item' => 'Товара',
            'ItemProperty' => 'Свойства товара',
            'LandingPageOption' => 'Настройки лендинга',
            'LlymarCalculatorItem' => 'Системы ЛУМАР',
            'LogisticsCompany' => 'Перевозчика',
            'News' => 'Новости',
            'OpeningParameters' => 'Параметра проема',
            'Order' => 'Заказа',
            'OrderItem' => 'Товара заказа',
            'OrderOpening' => 'Проема заказа',
            'Permission' => 'Разрешения',
            'Portfolio' => 'Портфолио',
            'RegistrationLink' => 'Ссылки регистрации',
            'Role' => 'Роли',
            'User' => 'Пользователя',
            'Warehouse' => 'Склада',
            'WarehouseRecord' => 'Записей склада',
            // 'access' => '',
            'access admin panel' => 'Доступ к админ панели',
            'access app calculator' => 'Доступ к калькулятору',
            'access app cart' => 'Доступ к корзине',
            'access app commission-credits' => 'Доступ к комиссионным операциям',
            'access app history' => 'Доступ к истории заказов',
            'access app sketcher' => 'Доступ к чертежу',
            'access app users' => 'Доступ к пользователям',
            'access app wholesale-factors' => 'Доступ к коэфициентам',
            // 'access dxf' => '',
            // 'app' => '',
            // 'can' => '',
            'create' => 'Создание',
            'dealer' => 'Дилера',
            'dealer-ch' => 'Дилера Ч',
            'dealer-r' => 'Дилера Р',
            'delete' => 'Удаление',
            'delete-any' => 'Удаление любой(-го)',
            // 'force-delete' => '',
            // 'force-delete-any' => '',
            'manager' => 'Менеджера',
            'operator' => 'Оператора',
            // 'reorder' => '',
            // 'replicate' => '',
            // 'restore' => '',
            // 'restore-any' => '',
            'rop' => 'РОПа',
            'super-admin' => 'Супер-Админа',
            'update' => 'Изменение',
            'view' => 'Просмотр',
            'view-any' => 'Просмотр любой(-го)',
            'wholesale-factors' => 'Коэфициенты',
            'workman' => 'Цеховщика',
        ];
        
        $permissions = Permission::all();

        foreach ($permissions as $permission) {
            $ignore_prefixes = ['reorder', 'force-delete', 'replicate', 'restore'];
            
            // Check if permission starts with any ignore prefix or contains AuthProvider
            $shouldIgnore = false;
            foreach ($ignore_prefixes as $prefix) {
                if (str_starts_with($permission->name, $prefix)) {
                    $shouldIgnore = true;
                    break;
                }
            }
            
            // Also ignore AuthProvider-related permissions
            if (str_contains($permission->name, 'AuthProvider')) {
                $shouldIgnore = true;
            }
            
            if ($shouldIgnore) {
                $permission->display_name = $permission->name;
                $permission->save();
                continue;
            }

            if (str_starts_with($permission->name, 'access app') || str_starts_with($permission->name, 'access admin')) {
                $permission->display_name = $vocabulary[$permission->name];
                $permission->save();
                continue;
            }

            $words = explode(' ', $permission->name);
            $display_name = '';
            foreach ($words as $word) {
                if (array_key_exists($word, $vocabulary)) {
                    $display_name .= $vocabulary[$word] . ' ';
                }
            }
            $permission->display_name = $display_name;
            $permission->save();
        }
    }
}
