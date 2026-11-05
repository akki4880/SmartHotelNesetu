<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeasonalRate extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
