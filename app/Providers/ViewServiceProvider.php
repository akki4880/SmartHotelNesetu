<?php

namespace App\Providers;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {

            if (Auth::check() && Auth::user()->user_role_id === 1) {
                $hotels = Hotel::with([
                    'admin:id,name,email,hotel_id',
                    'subscription.plan:id,name,price',
                ])->latest()->get();
                $view->with('headerHotels', $hotels);
            }

            if (auth()->check()) {
                auth()->user()->load('hotel');
            }
        });
    }
}
