<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Employee;
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
            $totalHotels = Hotel::count();
            return view('dashboard', compact('totalHotels'));
        } else {
            return view('dashboard', compact('totalHotels'));
        }
    }
}
