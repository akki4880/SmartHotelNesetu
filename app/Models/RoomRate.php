<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomRate extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
