<?php

namespace App\Http\Controllers;

use App\Models\HourlyRate;
use App\Models\RoomDistribution;
use App\Models\RoomNumber;
use App\Models\RoomRate;
use App\Models\RoomType;
use App\Models\SeasonalRate;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Carbon\Carbon;

class PriceChartController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:price_chart,view', only: ['index', 'getroomrates']),
        ];
    }

    public function index()
    {
        $hotelId = auth()->user()->hotel_id;
        $roomTypes = RoomType::where('status', 1)->where('hotel_id', $hotelId)
            ->withCount([
                'roomNumbers as total_rooms' 
            ])->get();
        return view('pages.pricechart.index', compact('roomTypes'));
    }

    public function getroomrates(Request $request)
    {
        try {
            $hotelId = auth()->user()->hotel_id;
            $year  = $request->year;
            $month = $request->month;

            $start = Carbon::create($year, $month, 1);
            $end   = $start->copy()->endOfMonth();

            $roomTypes = RoomType::where('hotel_id', $hotelId)->get();

            $roomRates = RoomRate::where('hotel_id', $hotelId)
                ->where('status', 1)
                ->get()
                ->keyBy('room_type_id');

            $hourlyRates = HourlyRate::where('hotel_id', $hotelId)
                ->where('status', 1)
                ->get()
                ->keyBy('room_type_id');

            $seasonalRates = SeasonalRate::where('hotel_id', $hotelId)
                ->where('status', 1)
                ->where(function ($q) use ($start, $end) {
                    $q->whereBetween('start_date', [$start, $end])
                        ->orWhereBetween('end_date', [$start, $end]);
                })
                ->get();

            // COUNT TOTAL ROOMS for each room type
            $totalRooms = RoomNumber::where('hotel_id', $hotelId)
                ->get()
                ->groupBy('room_type_id')
                ->map(fn($r) => $r->count());
                // dd($totalRooms);

            // Fetch all active bookings that occupy rooms in this month
            $blockedRooms = RoomDistribution::where('hotel_id', $hotelId)
                ->whereBetween('allocation_date', [$start, $end])
                ->whereHas('booking', function ($q) {
                    // Only consider bookings that were not cancelled or no-show
                    $q->whereNotIn('status', ['cancelled', 'no_show','checked_out']);
                })
                ->get()
                ->groupBy(fn($r) => $r->room_type_id . '|' . $r->allocation_date);
            // dd($blockedRooms);
            $calendar = [];

            foreach ($roomTypes as $roomType) {
                $roomTypeId = $roomType->id;

                for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                    $dateKey = $date->format('Y-m-d');

                    $total = $totalRooms[$roomTypeId] ?? 0;

                    $blockedKey = $roomTypeId . '|' . $dateKey;
                    $blocked = isset($blockedRooms[$blockedKey])
                        ? $blockedRooms[$blockedKey]->count()
                        : 0;

                    $available = max(0, $total - $blocked);

                    // Price logic
                    $price = null;
                    $type = 'weekday';

                    // Seasonal rate check
                    $season = $seasonalRates->first(
                        fn($s) =>
                        $s->room_type_id == $roomTypeId &&
                            $date->between($s->start_date, $s->end_date)
                    );

                    if ($season) {
                        $price = $season->seasonal_price;
                        $type = 'seasonal';
                    } elseif ($roomType->is_hourly && isset($hourlyRates[$roomTypeId])) {
                        $price = $hourlyRates[$roomTypeId]->base_price;
                        $type = 'hourly';
                    } elseif (isset($roomRates[$roomTypeId])) {
                        $rate = $roomRates[$roomTypeId];
                        if ($date->isWeekend() && $rate->weekend_price) {
                            $price = $rate->weekend_price;
                            $type = 'weekend';
                        } else {
                            $price = $rate->base_price;
                            $type = 'weekday';
                        }
                    }

                    $calendar[$roomTypeId][$dateKey] = [
                        'price'     => $price,
                        'type'      => $type,
                        'available' => $available,
                        'total'     => $total // total rooms for this type
                    ];
                }
            }

            return response()->json($calendar);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch the price.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
