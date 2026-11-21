<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'dob' => 'date:Y-m-d',
        'documents' => 'array',
    ];
}
