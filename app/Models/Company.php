<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'short_name', 
        'full_name', 
        'boss_name', 
        'boss_title', 
        'legal_address', 
        'email', 
        'phone',
        'phone_2',
        'phone_3',
        'website', 
        'inn', 
        'kpp', 
        'ogrn',
        'vat',
        'type',
        'warehouse_id',
    ];
    
    /**
     * Relationship: Order has many Order Items.
     *
     * @return HasMany
     */
    public function companyBills(): HasMany
    {
        return $this->hasMany(CompanyBill::class);
    }
}
