<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RoomType extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function roomNumbers()
    {
        return $this->hasMany(RoomNumber::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function getRateForDate(Carbon $date, $hotel): array
    {
        // 1️⃣ Seasonal rate (highest priority)
        $season = $this->seasonalRates()
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->where('status', true)
            ->first();

        if ($season) {
            return [
                'price' => (float) $season->seasonal_price,
                'type'  => 'seasonal'
            ];
        }

        $roomRate = $this->roomRate;

        if (!$roomRate) {
            return [
                'price' => 0,
                'type'  => 'not_defined'
            ];
        }

        // 2️⃣ Hourly rate (if enabled)
        if (
            in_array('hourly', $hotel->room_rate_type ?? []) &&
            !empty($roomRate->hourly_price)
        ) {
            return [
                'price' => (float) $roomRate->hourly_price,
                'type'  => 'hourly'
            ];
        }

        // 3️⃣ Weekend / Weekday
        if ($date->isWeekend() && !empty($roomRate->weekend_price)) {
            return [
                'price' => (float) $roomRate->weekend_price,
                'type'  => 'weekend'
            ];
        }

        return [
            'price' => (float) $roomRate->base_price,
            'type'  => 'weekday'
        ];
    }

    public function roomRate()
    {
        return $this->hasOne(RoomRate::class, 'room_type_id');
    }


    public function getHourlyRate()
    {
        return $this->hourly_rate ?? $this->base_rate;
    }

    public function seasonalRates()
    {
        return $this->hasMany(SeasonalRate::class);
    }
}
