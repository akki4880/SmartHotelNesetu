<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingGuest extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function idProofs()
    {
        return $this->hasMany(BookingGuestIdProof::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
