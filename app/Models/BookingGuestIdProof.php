<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingGuestIdProof extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function bookingGuest()
    {
        return $this->belongsTo(BookingGuest::class);
    }
}
