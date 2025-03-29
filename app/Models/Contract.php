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
}
