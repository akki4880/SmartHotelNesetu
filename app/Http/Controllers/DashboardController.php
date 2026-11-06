<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Employee;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\RoomNumber;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DashboardController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:dashboard,view', only: ['index']),
        ];
    }

    public function index()
    {
        if (Auth::user()->user_role_id == 1) {
            $data = [
                'total_hotels' => Hotel::count(),
                'total_active_hotels' => Hotel::where('status', 1)->count(),
                'total_users' => User::whereNotIn('user_role_id', [1])->count(), // Assuming 1 is super admin
                'total_employees' => Employee::count(),
                'total_bookings' => Booking::count(),
                'active_subscriptions' => Subscription::where('status', 'active')->count(),
                'support_tickets' => Ticket::count(),
                'total_rooms' => RoomNumber::count(),
                'expired_subscriptions' => Subscription::where('status', 'expired')->count(),
            ];


            $hotels = Hotel::with(['admin', 'subscription.plan'])->latest()->take(6)->get();

            return view('pages.admins.dashboard.index', compact('data', 'hotels'));
        } elseif (Auth::user()->user_role_id == 3) {
            $hotelId = Auth::user()->hotel_id;

            $totalRooms = RoomNumber::where('hotel_id', $hotelId)->count();
            $occupiedRooms = Booking::where('hotel_id', $hotelId)
                ->where('status', 'checked_in')
                ->count();
            $availableRooms = max(0, $totalRooms - $occupiedRooms);
            $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

            $roomTypeBreakdown = \App\Models\RoomType::where('hotel_id', $hotelId)
                ->withCount('roomNumbers')
                ->get()
                ->map(function ($roomType, $index) use ($occupancyRate) {
                    $total = (int) $roomType->room_numbers_count;
                    $occupied = $total > 0 ? (int) round(($total * $occupancyRate) / 100) : 0;
                    $occupied = min($total, max(0, $occupied));
                    $available = $total - $occupied;

                    return [
                        'name' => $roomType->name,
                        'total' => $total,
                        'occupied' => $occupied,
                        'available' => $available,
                        'percent' => $total > 0 ? round(($occupied / $total) * 100) : 0,
                        'color' => ['#3b82f6', '#8b5cf6', '#10b981', '#f59e0b', '#06b6d4', '#ec4899'][$index % 6],
                    ];
                })
                ->take(4)
                ->values();

            $currentMonth = now()->month;
            $currentYear = now()->year;
            $currentMonthRevenue = (float) Booking::where('hotel_id', $hotelId)
                ->whereMonth('created_at', $currentMonth)
                ->whereYear('created_at', $currentYear)
                ->sum('total_amount');

            $previousMonthDate = now()->copy()->subMonth();
            $previousMonthRevenue = (float) Booking::where('hotel_id', $hotelId)
                ->whereMonth('created_at', $previousMonthDate->month)
                ->whereYear('created_at', $previousMonthDate->year)
                ->sum('total_amount');

            $revenueData = [
                'current_month_total' => $currentMonthRevenue,
                'previous_month_total' => $previousMonthRevenue,
                'change_percent' => $previousMonthRevenue > 0
                    ? round((($currentMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100, 1)
                    : 0,
                'monthly' => collect(range(1, 12))->map(function ($month) use ($hotelId, $currentYear) {
                    $monthDate = now()->setDate($currentYear, $month, 1);

                    return [
                        'month' => $monthDate->shortMonthName,
                        'value' => (float) Booking::where('hotel_id', $hotelId)
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $currentYear)
                            ->sum('total_amount'),
                    ];
                })->values()->all(),
            ];

            $stats = [
                'new_bookings_today' => Booking::where('hotel_id', $hotelId)
                    ->whereDate('created_at', today())
                    ->count(),
                'rooms_scheduled' => Booking::where('hotel_id', $hotelId)
                    ->whereDate('check_in_date', today())
                    ->count(),
                'total_customers' => Guest::where('hotel_id', $hotelId)->count(),
                'revenue_this_month' => $currentMonthRevenue,
                'available_rooms' => $availableRooms,
                'occupied_rooms' => $occupiedRooms,
                'check_ins_today' => Booking::where('hotel_id', $hotelId)
                    ->whereDate('check_in_date', today())
                    ->count(),
                'check_outs_today' => Booking::where('hotel_id', $hotelId)
                    ->whereDate('check_out_date', today())
                    ->count(),
            ];

            $recentBookings = Booking::with(['primaryGuest', 'rooms.roomType'])
                ->where('hotel_id', $hotelId)
                ->latest('created_at')
                ->take(6)
                ->get();

            $customers = Guest::where('hotel_id', $hotelId)
                ->latest('created_at')
                ->take(5)
                ->get();

            return view('dashboard', compact('stats', 'revenueData', 'recentBookings', 'customers', 'totalRooms', 'occupancyRate', 'roomTypeBreakdown'));
        } else {
            $totalHotels = Hotel::count();
            return view('dashboard', compact('totalHotels'));
        }
    }
}
