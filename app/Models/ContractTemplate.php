<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContractTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'attachment',
        'attachment_original_filename',
        'fields',
    ];
    
    protected $casts = [
        'fields' => 'array',
    ];

    /**
     * Relationship: ContractTemplate belongs to a User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Relationship: ContractTemplate has many Contract.
     *
     * @return HasMany
    */
    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
