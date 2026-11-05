<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoomTypeChange extends Model
{
    protected $table = 'booking_room_type_changes';
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'effective_from' => 'date',
        'price_adjustment' => 'float',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function oldRoomType()
    {
        return $this->belongsTo(RoomType::class, 'old_room_type_id');
    }

    public function newRoomType()
    {
        return $this->belongsTo(RoomType::class, 'new_room_type_id');
    }

    public function oldRoom()
    {
        return $this->belongsTo(RoomNumber::class, 'old_room_number_id');
    }

    public function newRoom()
    {
        return $this->belongsTo(RoomNumber::class, 'new_room_number_id');
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
