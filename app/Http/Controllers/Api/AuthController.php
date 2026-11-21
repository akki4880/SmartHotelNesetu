<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        // Find user using phone from users table
        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        // Check hotel and subscription for client roles
        if ($user->user_role_id >= 3) {

            $hotel = $user->hotel;

            if (!$hotel) {
                return response()->json([
                    'message' => 'No hotel associated with your account. Contact SmartHotel.',
                ], 403);
            }

            if (!$hotel->status) {
                return response()->json([
                    'message' => 'Your hotel account is inactive: ' .
                        ($hotel->blocked_reason ?? 'No reason provided.'),
                ], 403);
            }

            $subscription = $hotel->subscription;

            if (!$subscription) {
                return response()->json([
                    'message' => 'No subscription found. Please contact SmartHotel.',
                ], 403);
            }

            // Check subscription validity
            $today = now()->startOfDay();

            $start = \Carbon\Carbon::parse($subscription->start_date)->startOfDay();
            $end = \Carbon\Carbon::parse($subscription->end_date)->endOfDay();

            if ($today->lt($start) || $today->gt($end)) {
                return response()->json([
                    'message' => 'Your subscription is not active. Please contact SmartHotel.',
                ], 403);
            }
        }

        $user->tokens()->delete();

        $refreshTokenPlain = Str::random(64);
        // Generate Sanctum token
        $token = $user->createToken(
            $validated['device_name'] ?? 'smarthotel-mobile'
        )->plainTextToken;
        DB::table('refreshtokens')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'token' => Hash::make($refreshTokenPlain),
                'device_id' => $request->device_id,
                'expires_at' => now()->addDays(60),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );


        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'hotel' => $user->hotel,
            'token' => $token,
            'refresh_token' => $refreshTokenPlain,
            'token_type' => 'Bearer',
        ], 200);
    }


    public function refreshToken(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
            'device_id' => 'required'
        ]);

        $record = DB::table('refreshtokens')
            ->where('device_id', $request->device_id)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => false,
                'message' => 'Refresh token not found'
            ], 404);
        }

        if (now()->gt($record->expires_at)) {
            DB::table('refreshtokens')->where('id', $record->id)->delete();

            return response()->json([
                'status' => false,
                'message' => 'Refresh token expired. Login again.'
            ], 401);
        }

        if (!Hash::check($request->refresh_token, $record->token)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid refresh token'
            ], 401);
        }

        $user = User::find($record->user_id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        // revoke old access tokens
        $user->tokens()->delete();

        $newAccessToken = $user->createToken('access_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => $newAccessToken
        ]);
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


    public function changePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is incorrect.',
                ], 400);
            }

            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'New password must be different from the current password.',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Password changed successfully.',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Change Password API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while changing the password.',
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'phone' => 'sometimes|required|string|unique:users,phone,' . $user->id,
            ]);

            $fields = [];

            if ($request->has('name')) {
                $fields['name'] = $request->name;
            }

            if ($request->has('phone')) {
                $fields['phone'] = $request->phone;
            }

            if ($request->has('email')) {
                $fields['email'] = trim($request->email);
            }

            if (empty($fields)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No profile fields were provided to update.',
                ], 422);
            }

            $user->update($fields);

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully.',
                'data' => [
                    'name' => $user->fresh()->name,
                    'email' => $user->fresh()->email,
                    'phone' => $user->fresh()->phone,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Update Profile API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while updating the profile.',
            ], 500);
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'We could not find an account with that email address.',
                ], 404);
            }

            $otp = random_int(100000, 999999);

            DB::table('otps')->updateOrInsert(
                ['phone' => $user->phone],
                [
                    'otp' => Hash::make($otp),
                    'attempts' => 0,
                    'expires_at' => now()->addMinutes(5),
                    'type' => 'forgot_password',
                    'new_email' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            Mail::raw("Your Charado password reset OTP is {$otp}. It expires in 5 minutes.", function ($message) use ($user) {
                $message->to($user->email)->subject('Reset your Charado password');
            });

            return response()->json([
                'status' => true,
                'message' => 'Password reset OTP has been sent to your email.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Forgot Password API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while sending the reset OTP.',
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'otp' => 'required|string',
                'new_password' => 'required|string|min:6|confirmed',
            ]);

            $record = DB::table('otps')->where('phone', $request->phone)->first();

            if (!$record) {
                return response()->json([
                    'status' => false,
                    'message' => 'OTP not found',
                ], 404);
            }

            if (($record->type ?? null) !== 'forgot_password') {
                return response()->json([
                    'status' => false,
                    'message' => 'This OTP is not valid for password reset.',
                ], 422);
            }

            if (now()->gt($record->expires_at)) {
                DB::table('otps')->where('phone', $request->phone)->delete();

                return response()->json([
                    'status' => false,
                    'message' => 'OTP expired. Please request a new one.',
                ], 401);
            }

            if (!Hash::check($request->otp, $record->otp)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid OTP',
                ], 401);
            }

            $user = User::where('phone', $request->phone)->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (Hash::check($request->new_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'New password must be different from the current password.',
                ], 422);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            DB::table('otps')->where('phone', $request->phone)->delete();
            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Password reset successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Reset Password API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while resetting the password.',
            ], 500);
        }
    }

    public function deleteAccount(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $request->validate([
                'password' => 'required|string',
            ]);

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password is incorrect.',
                ], 400);
            }

            $user->delete();
            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Account deleted successfully.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Delete Account API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong while deleting the account.' . $e->getMessage(),
            ], 500);
        }
    }
}
