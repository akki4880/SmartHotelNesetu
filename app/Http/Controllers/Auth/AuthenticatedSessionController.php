<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('phone', 'password');

        // Attempt to get the user first
        $user = \App\Models\User::where('phone', $credentials['phone'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'phone' => 'Invalid credentials.'
            ]);
        }

        // Only hotel users need extra checks
        if ($user->user_role_id >= 3) {
            $hotel = $user->hotel;

            if (!$hotel) {
                return back()->withErrors([
                    'phone' => 'No hotel associated with your account. Contact SmartHotel.'
                ]);
            }

            if (!$hotel->status) {
                return back()->withErrors([
                    'phone' => 'Your hotel account is inactive: ' . ($hotel->blocked_reason ?? 'No reason provided.')
                ]);
            }

            $subscription = $hotel->subscription;

            if (!$subscription) {
                return back()->withErrors([
                    'phone' => 'No subscription found. Please contact SmartHotel.'
                ]);
            }

            // Check if today is within subscription period
            $today = now()->startOfDay();
            $start = \Carbon\Carbon::parse($subscription->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($subscription->end_date)->endOfDay();

            if ($today->lt($start) || $today->gt($end)) {
                return back()->withErrors([
                    'phone' => 'Your subscription is not active. Please contact SmartHotel.'
                ]);
            }
        }

        // All checks passed, now log the user in
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
