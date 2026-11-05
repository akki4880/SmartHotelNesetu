<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAdditionalService;
use App\Models\Hotel;
use App\Models\HourlyRate;
use App\Models\RoomType;
use App\Models\RoomRate;
use App\Models\SeasonalRate;
use Carbon\Carbon;

class PricingService
{
    /**
     * Calculate the total price for a booking.
     *
     * @param int $hotelId
     * @param int $roomTypeId
     * @param Carbon|string $checkInDate
     * @param Carbon|string $checkOutDate
     * @param int $numberOfGuests
     * @param int $numberOfRooms
     * @param float|null $discountAmount
     * @param string|null $discountType ('flat', 'percent', or null)
     * @return array ['base_amount', 'discount_amount', 'subtotal', 'cgst', 'sgst', 'igst', 'total_amount', 'daily_breakdown']
     * @throws \Exception
     */
    public static function calculateHourlyRateCharge(
        HourlyRate $hourlyRate,
        Carbon $checkInAt,
        Carbon $checkOutAt,
        int $rooms = 1,
        $discountAmount = 0,
        ?string $discountType = null,
        float $serviceAmount = 0.0,
        ?Hotel $hotel = null
    ): array {
        if ($checkOutAt->lte($checkInAt)) {
            throw new \Exception('Actual checkout time must be after the actual check-in time');
        }

        $elapsedMinutes = max(0, $checkInAt->diffInMinutes($checkOutAt, false));
        $baseHours = (int) $hourlyRate->base_hours;
        $baseMinutes = $baseHours * 60;
        $extraMinutes = max(0, $elapsedMinutes - $baseMinutes);
        $extraHours = $extraMinutes > 0 ? (int) ceil($extraMinutes / 60) : 0;

        $basePrice = (float) $hourlyRate->base_price * $rooms;
        $extraPrice = (float) $hourlyRate->extra_hour_price * $extraHours * $rooms;
        $grossAmount = $basePrice + $extraPrice;

        $discountApplied = 0;
        if ((float) $discountAmount > 0 && $discountType) {
            if ($discountType === 'percent') {
                $discountApplied = ($grossAmount * (float) $discountAmount) / 100;
            } elseif ($discountType === 'flat') {
                $discountApplied = (float) $discountAmount;
            }
            $discountApplied = min($discountApplied, $grossAmount);
        }

        $subtotal = $grossAmount - $discountApplied;
        $hotelModel = $hotel ?? new Hotel();
        $taxes = self::calculateTaxes($subtotal, $hotelModel);
        $totalAmount = $subtotal + $taxes['total_tax'] + $serviceAmount;

        $actualHours = $elapsedMinutes > 0 ? (int) ceil($elapsedMinutes / 60) : 0;

        return [
            'base_hours' => $baseHours,
            'actual_hours' => $actualHours,
            'extra_hours' => $extraHours,
            'base_price' => round($basePrice, 2),
            'base_amount' => round($basePrice, 2),
            'extra_hour_price' => round((float) $hourlyRate->extra_hour_price, 2),
            'extra_amount' => round($extraPrice, 2),
            'discount_amount' => round($discountApplied, 2),
            'subtotal' => round($subtotal, 2),
            'cgst' => round($taxes['cgst'], 2),
            'sgst' => round($taxes['sgst'], 2),
            'igst' => round($taxes['igst'], 2),
            'total_tax' => round($taxes['total_tax'], 2),
            'service_amount' => round($serviceAmount, 2),
            'total_amount' => round($totalAmount, 2),
            'max_hours_exceeded' => !empty($hourlyRate->max_hours) && $actualHours > (int) $hourlyRate->max_hours,
            'elapsed_minutes' => $elapsedMinutes,
        ];
    }

    public static function calculateActualHourlyCharge(Booking $booking, ?Carbon $checkoutAt = null): array
    {
        $hotel = $booking->hotel;
        if (!$hotel) {
            throw new \Exception('Hotel not found for this booking');
        }

        $distribution = $booking->roomDistributions()->first();
        if (!$distribution) {
            throw new \Exception('No room assigned for this booking');
        }

        $hourlyRate = HourlyRate::where('hotel_id', $hotel->id)
            ->where('room_type_id', $distribution->room_type_id)
            ->where('status', true)
            ->first();

        if (!$hourlyRate) {
            throw new \Exception('Hourly rate not configured for this room type');
        }

        $checkInAt = $booking->actual_check_in_at
            ? Carbon::parse($booking->actual_check_in_at)
            : Carbon::parse($booking->check_in_date . ' 00:00:00');

        $checkOutAt = $checkoutAt ?? (
            $booking->actual_check_out_at
                ? Carbon::parse($booking->actual_check_out_at)
                : now()
        );

        $serviceAmount = (float) BookingAdditionalService::where('booking_id', $booking->id)->sum('amount');

        return self::calculateHourlyRateCharge(
            $hourlyRate,
            $checkInAt,
            $checkOutAt,
            (int) $booking->no_of_rooms,
            $booking->discount,
            $booking->discount_type,
            $serviceAmount,
            $hotel
        );
    }

    public static function calculateBookingPrice(
        $hotelId,
        $roomTypeId,
        $checkInDate,
        $checkOutDate,
        $numberOfGuests,
        $numberOfRooms = 1,
        $discountAmount = 0,
        $discountType = null,
        $rateType = null
    ) {
        $hotel = Hotel::findOrFail($hotelId);
        $roomType = RoomType::findOrFail($roomTypeId);
        $roomRate = $roomType->roomRate;
        $resolvedRateType = $rateType ?? $hotel->getDefaultBookingRateType();

        if ($resolvedRateType === 'hourly') {
            $hourlyRate = HourlyRate::where('hotel_id', $hotelId)
                ->where('room_type_id', $roomTypeId)
                ->where('status', true)
                ->first();

            if (!$hourlyRate) {
                throw new \Exception('Hourly rate not configured for this room type');
            }

            $baseAmount = (float) $hourlyRate->base_price * $numberOfRooms;
            $discountApplied = 0;
            if ($discountAmount > 0 && $discountType) {
                if ($discountType === 'percent') {
                    $discountApplied = ($baseAmount * $discountAmount) / 100;
                } elseif ($discountType === 'flat') {
                    $discountApplied = $discountAmount;
                }
                $discountApplied = min($discountApplied, $baseAmount);
            }

            $subtotal = $baseAmount - $discountApplied;
            $taxes = self::calculateTaxes($subtotal, $hotel);
            $totalAmount = $subtotal + $taxes['total_tax'];

            return [
                'base_amount' => round($baseAmount, 2),
                'discount_amount' => round($discountApplied, 2),
                'discount_type' => $discountType,
                'subtotal' => round($subtotal, 2),
                'cgst' => round($taxes['cgst'], 2),
                'sgst' => round($taxes['sgst'], 2),
                'igst' => round($taxes['igst'], 2),
                'total_tax' => round($taxes['total_tax'], 2),
                'total_amount' => round($totalAmount, 2),
                'nights' => 1,
                'rooms' => $numberOfRooms,
                'guests' => $numberOfGuests,
                'extra_guests' => 0,
                'daily_breakdown' => [[
                    'date' => $checkInDate,
                    'room_price' => (float) $hourlyRate->base_price,
                    'extra_price' => 0,
                    'daily_total' => (float) $hourlyRate->base_price,
                    'rate_type' => 'hourly'
                ]],
                'is_gst_applicable' => $hotel->is_gst_applicable,
                'tax_type' => $hotel->invoice_tax_type,
            ];
        }

        if (!$roomRate) {
            throw new \Exception('Room rate not configured for this room type');
        }

        if ($roomType->hotel_id !== $hotelId) {
            throw new \Exception('Room type does not belong to the specified hotel');
        }

        $checkIn = Carbon::parse($checkInDate);
        $checkOut = Carbon::parse($checkOutDate);

        // Validate dates
        if ($checkOut->lte($checkIn)) {
            throw new \Exception('Check-out date must be after check-in date');
        }

        // Validate guest capacity
        $baseGuests = $roomRate->base_guest_count;
        $maxExtraGuests = $roomRate->max_extra_guests;
        $maxCapacity = $baseGuests + $maxExtraGuests;

        if ($numberOfGuests > $maxCapacity) {
            throw new \Exception("Room type can accommodate maximum {$maxCapacity} guests");
        }

        // Calculate extra guests
        $extraGuests = max(0, $numberOfGuests - $baseGuests);
        $extraGuestPrice = $roomRate->extra_guest_price ?? 0;

        // Calculate room charges for each night
        $roomTotal = 0;
        $dailyBreakdown = [];

        $date = $checkIn->copy();
        while ($date->lt($checkOut)) {
            $dailyRate = self::getRateForDate($roomType, $date, $hotel);
            $dailyPrice = $dailyRate['price'];

            // Extra guest charge per night (per room)
            $dailyExtra = ($extraGuests > 0) ? ($extraGuests * $extraGuestPrice) : 0;

            // Store breakdown
            $dailyBreakdown[$date->toDateString()] = [
                'date' => $date->toDateString(),
                'room_price' => $dailyPrice,
                'extra_price' => $dailyExtra,
                'daily_total' => $dailyPrice + $dailyExtra,
                'rate_type' => $dailyRate['type']
            ];

            $roomTotal += ($dailyPrice + $dailyExtra);

            $date->addDay();
        }

        // Multiply by number of rooms
        $baseAmount = $roomTotal * $numberOfRooms;

        // Apply discount
        $discountApplied = 0;
        if ($discountAmount > 0 && $discountType) {
            if ($discountType === 'percent') {
                $discountApplied = ($baseAmount * $discountAmount) / 100;
            } elseif ($discountType === 'flat') {
                $discountApplied = $discountAmount;
            }
            // Discount cannot exceed base amount
            $discountApplied = min($discountApplied, $baseAmount);
        }

        $subtotal = $baseAmount - $discountApplied;

        // Calculate taxes
        $taxes = self::calculateTaxes($subtotal, $hotel);

        $totalAmount = $subtotal + $taxes['total_tax'];

        return [
            'base_amount' => round($baseAmount, 2),
            'discount_amount' => round($discountApplied, 2),
            'discount_type' => $discountType,
            'subtotal' => round($subtotal, 2),
            'cgst' => round($taxes['cgst'], 2),
            'sgst' => round($taxes['sgst'], 2),
            'igst' => round($taxes['igst'], 2),
            'total_tax' => round($taxes['total_tax'], 2),
            'total_amount' => round($totalAmount, 2),
            'nights' => $checkOut->diffInDays($checkIn),
            'rooms' => $numberOfRooms,
            'guests' => $numberOfGuests,
            'extra_guests' => $extraGuests,
            'daily_breakdown' => $dailyBreakdown,
            'is_gst_applicable' => $hotel->is_gst_applicable,
            'tax_type' => $hotel->invoice_tax_type
        ];
    }

    /**
     * Get the applicable rate for a specific date and room type.
     *
     * @param RoomType $roomType
     * @param Carbon $date
     * @param Hotel $hotel
     * @return array ['price', 'type']
     */
    public static function getRateForDate(RoomType $roomType, Carbon $date, Hotel $hotel)
    {
        // 1. Check seasonal rate (highest priority)
        $seasonalRate = SeasonalRate::where('room_type_id', $roomType->id)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->where('status', true)
            ->first();

        if ($seasonalRate) {
            return [
                'price' => (float) $seasonalRate->seasonal_price,
                'type' => 'seasonal',
                'name' => $seasonalRate->season_name ?? 'Seasonal'
            ];
        }

        $roomRate = $roomType->roomRate;

        if (!$roomRate) {
            return [
                'price' => 0,
                'type' => 'not_defined',
                'name' => 'Not Configured'
            ];
        }

        // 2. Check for weekend rate if applicable and configured
        if ($date->isWeekend() && !empty($roomRate->weekend_price)) {
            return [
                'price' => (float) $roomRate->weekend_price,
                'type' => 'weekend',
                'name' => 'Weekend'
            ];
        }

        // 3. Return base weekday rate
        return [
            'price' => (float) $roomRate->base_price,
            'type' => 'weekday',
            'name' => 'Base Rate'
        ];
    }

    /**
     * Calculate tax amounts based on hotel's GST configuration.
     *
     * @param float $amount
     * @param Hotel $hotel
     * @return array ['cgst', 'sgst', 'igst', 'total_tax']
     */
    public static function calculateTaxes($amount, Hotel $hotel)
    {
        $cgst = 0;
        $sgst = 0;
        $igst = 0;
        $totalTax = 0;

        if (!$hotel->is_gst_applicable) {
            return compact('cgst', 'sgst', 'igst', 'totalTax');
        }

        // If tax is inclusive, we need to extract the tax from the amount
        // If tax is exclusive, we add the tax to the amount
        // For now, assuming exclusive (add tax to amount)

        if ($hotel->invoice_tax_type === 'exclusive') {
            // Calculate tax percentages on the amount
            $cgstPct = $hotel->cgst_percentage ?? 0;
            $sgstPct = $hotel->sgst_percentage ?? 0;
            $igstPct = $hotel->igst_percentage ?? 0;

            $cgst = ($amount * $cgstPct) / 100;
            $sgst = ($amount * $sgstPct) / 100;
            $igst = ($amount * $igstPct) / 100;
        } else {
            // Inclusive - extract tax from amount
            // amount = base + tax; base = amount / (1 + tax_rate)
            $combinedRate = (($hotel->cgst_percentage ?? 0) + 
                           ($hotel->sgst_percentage ?? 0) + 
                           ($hotel->igst_percentage ?? 0)) / 100;

            $base = $amount / (1 + $combinedRate);
            $cgst = ($base * ($hotel->cgst_percentage ?? 0)) / 100;
            $sgst = ($base * ($hotel->sgst_percentage ?? 0)) / 100;
            $igst = ($base * ($hotel->igst_percentage ?? 0)) / 100;
        }

        $totalTax = $cgst + $sgst + $igst;

        return [
            'cgst' => $cgst,
            'sgst' => $sgst,
            'igst' => $igst,
            'total_tax' => $totalTax
        ];
    }

    /**
     * Calculate price for additional services.
     *
     * @param array $services [['service_id' => 1, 'quantity' => 2], ...]
     * @param Hotel $hotel
     * @return array ['services' => [...], 'total_service_amount' => float]
     */
    public static function calculateServiceCharges($services, Hotel $hotel)
    {
        $serviceCosts = [];
        $totalServiceAmount = 0;

        if (!is_array($services) || empty($services)) {
            return [
                'services' => [],
                'total_service_amount' => 0
            ];
        }

        foreach ($services as $serviceItem) {
            $service = $hotel->additionalServices()
                ->find($serviceItem['service_id'] ?? $serviceItem['id'] ?? null);

            if (!$service) {
                continue;
            }

            $quantity = $serviceItem['quantity'] ?? 1;
            $cost = ($service->cost ?? 0) * $quantity;

            $serviceCosts[] = [
                'service_id' => $service->id,
                'service_name' => $service->name,
                'cost_per_unit' => $service->cost ?? 0,
                'quantity' => $quantity,
                'total_cost' => round($cost, 2)
            ];

            $totalServiceAmount += $cost;
        }

        return [
            'services' => $serviceCosts,
            'total_service_amount' => round($totalServiceAmount, 2)
        ];
    }
}
