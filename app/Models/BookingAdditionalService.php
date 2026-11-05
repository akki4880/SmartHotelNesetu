<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingAdditionalService extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function service()
    {
        return $this->belongsTo(AdditionalService::class, 'additional_service_id');
    }
}
