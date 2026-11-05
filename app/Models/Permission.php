<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'module_key',
        'module_name',
        'actions',
        'status',
        'is_visible_to_hotel_owner',
        'is_visible_to_super_admin',
    ];

    protected $casts = [
        'actions' => 'array',
    ];
}
