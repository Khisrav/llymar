<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'template_id',
        'number',
        'date',
        'counterparty_type',
        'counterparty_fullname',
        'counterparty_phone',
        'installation_address',
        'price',
        'advance_payment_percentage',
        'department_code',
        'index',
    ];
    
    public static function getColumns() {
        return [
            'template_id' => 'ID шаблона',
            'number' => 'Номер договора',
            'date' => 'Дата',
            'counterparty_type' => 'Тип заказчика',
            'counterparty_fullname' => 'ФИО заказчика',
            'counterparty_phone' => 'Телефон заказчика',
            'installation_address' => 'Адрес установки',
            'price' => 'Цена',
            'advance_payment_percentage' => '% аванса',
            'department_code' => 'Код подразделения',
            'index' => 'Индекс',
        ];
    }
}
