<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPageOption extends Model
{
    protected $fillable = [
        'key',
        'label',
        'value',
        'type',
        'description',
        'group',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get option value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $option = static::where('key', $key)->first();
        return $option ? $option->value : $default;
    }

    /**
     * Set option value by key
     */
    public static function setValue(string $key, $value): bool
    {
        $option = static::where('key', $key)->first();
        
        if ($option) {
            $option->value = $value;
            return $option->save();
        }
        
        return false;
    }

    /**
     * Get all options grouped by group
     */
    public static function getAllGrouped(): array
    {
        return static::orderBy('group')
            ->orderBy('order')
            ->get()
            ->groupBy('group')
            ->toArray();
    }
}
