<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
