<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyBill extends Model
{
    protected $fillable = [
        'company_id',
        'current_account', //счет зачисления, т.е. credit account
        'correspondent_account',
        'bank_name',
        'bank_address',
        'bik',
    ];
    
    /**
     * Relationship: Order belongs to a User.
     *
     * @return BelongsTo
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
