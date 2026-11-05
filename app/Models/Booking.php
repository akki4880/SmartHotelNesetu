<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'guest_id' => 'array',
    ];

    public function roomDistributions()
    {
        return $this->hasMany(RoomDistribution::class);
    }

    public function rooms()
    {
        return $this->belongsToMany(
            RoomNumber::class,
            'room_distributions',
            'booking_id',
            'room_number_id'
        );
    }
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'booking_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function primaryGuest()
    {
        return $this->hasOne(BookingGuest::class)->where('is_primary_guest', true);
    }

    public function bookingGuests()
    {
        return $this->hasMany(BookingGuest::class);
    }

    public function additionalServices()
    {
        return $this->belongsToMany(
            AdditionalService::class,
            'booking_additional_services',
            'booking_id',
            'additional_service_id'
        )->withPivot(['quantity', 'amount']);
    }

    public function payments()
    {
        return $this->hasMany(BookingPayment::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
    
    public function guests()
    {
        return $this->hasMany(BookingGuest::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
