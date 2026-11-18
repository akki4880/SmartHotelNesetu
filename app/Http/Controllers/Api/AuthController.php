<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login using email and password.
     */


    public function login(Request $request)
    {
        $validated = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $hotel = \App\Models\Hotel::where(
            'phone',
            $validated['phone']
        )->first();

        if (!$hotel) {
            return response()->json([
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        $user = User::where('hotel_id', $hotel->id)->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        $token = $user->createToken(
            $validated['device_name'] ?? 'smarthotel-mobile'
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'hotel' => $hotel,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }



    /**
     * Logout by revoking the current access token.
     */
    public function logout(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }
}
