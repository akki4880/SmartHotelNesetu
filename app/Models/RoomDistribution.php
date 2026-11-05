<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomDistribution extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function roomNumber()
    {
        return $this->belongsTo(RoomNumber::class,'room_number_id');
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class,'room_type_id');
    }
}
