<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'included_features' => 'array',
        'excluded_features' => 'array',
        'permissions' => 'array',
    ];
}
