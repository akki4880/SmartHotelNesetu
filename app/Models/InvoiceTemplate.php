<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceTemplate extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'default_settings' => 'array',
    ];
}
