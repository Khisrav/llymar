<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'display_name', 'guard_name'];

    public function getDisplayNameAttribute()
    {
        return $this->attributes['display_name'] ?? $this->name;
    }
}
