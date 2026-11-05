<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];
    protected $casts = [
        'room_rate_type' => 'array',
    ];

    public function supportsRoomRate(): bool
    {
        return in_array('room', $this->room_rate_type ?? [], true);
    }

    public function supportsHourlyRate(): bool
    {
        return in_array('hourly', $this->room_rate_type ?? [], true);
    }

    public function getBookingRateOptions(): array
    {
        $types = $this->room_rate_type ?? ['room'];

        return array_values(array_unique(array_filter($types, fn ($type) => in_array($type, ['room', 'hourly'], true))));
    }

    public function getDefaultBookingRateType(): string
    {
        $types = $this->getBookingRateOptions();

        if (count($types) === 1) {
            return $types[0];
        }

        return $this->supportsRoomRate() ? 'room' : 'hourly';
    }

    public function admin()
    {
        return $this->hasOne(User::class, 'hotel_id', 'id')
            ->where('user_role_id', 3);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function additionalServices()
    {
        return $this->hasMany(AdditionalService::class);
    }

    public function agents()
    {
        return $this->hasMany(Agent::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'hotel_id');
    }

    public function invoiceTemplate()
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }
}
