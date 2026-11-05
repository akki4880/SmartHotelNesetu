<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function getStates(Request $request)
    {
        $country = Country::where('id', $request->country_id)->first();
        return State::where('country_code', $country->code)
            ->orderBy('state_name')
            ->get(['id', 'state_name']);
    }
}
