<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FrontendController extends Controller
{
     
    public function privacyPolicy()
    {
        return view('frontend.privacy-policy.index');
    }

    public function termsAndConditions()
    {
        return view('frontend.terms-and-conditions.index');
    }

    public function dataProtectionPolicy()
    {
        return view('frontend.data-protection-policy.index');
    }

    public function cancellationRefundPolicy()
    {
        return view('frontend.cancellation-refund-policy.index');
    }

    public function disclaimer()
    {
        return view('frontend.disclaimer.index');
    }

    public function cookiePolicy()
    {
        return view('frontend.cookie-policy.index');
    }



    public function login()
    {
        return view('auth.login');
    }
}
