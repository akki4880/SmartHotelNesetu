<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function home()
    {
        return view('frontend.home.index');
    }

    public function aboutUs()
    {
        return view('frontend.about-us.index');
    }

    public function pricing()
    {
        return view('frontend.pricing.index');
    }

    public function contactUs()
    {
        return view('frontend.contact-us.index');
    }

    public function services()
    {
        return view('frontend.services.index');
    }

    public function login()
    {
        return view('auth.login');
    }
}
