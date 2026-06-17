<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivityLog extends Model
{
    /** @use HasFactory<\Database\Factories\UserActivityLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'visits_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'visits_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
