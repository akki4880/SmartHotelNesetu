<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function updatePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'admin_id' => 'required|exists:users,id',
                'password' => 'required|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $admin = User::findOrFail($request->admin_id);

            $admin->password = Hash::make($request->password);
            $admin->save();

            return response()->json([
                'status' => true,
                'message' => 'Password updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong' . $e->getMessage()
            ], 500);
        }
    }

    public function switch(Request $request)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id'
        ]);

        // Store original admin
        if (!session()->has('impersonator_id')) {
            session([
                'impersonator_id' => Auth::id()
            ]);
        }

        // Store active hotel admin
        session([
            'active_hotel_admin_id' => $request->admin_id
        ]);

        // Login as hotel admin
        Auth::loginUsingId($request->admin_id);

        return response()->json([
            'status' => true
        ]);
    }


    public function stop()
    {
        $originalAdminId = session('impersonator_id');

        session()->forget([
            'impersonator_id',
            'active_hotel_admin_id'
        ]);

        Auth::loginUsingId($originalAdminId);

        return redirect()->route('dashboard');
    }

    public function toggleStatus(Request $request)
    {
        try {

            $request->validate([
                'model' => 'required',
                'id'    => 'required|integer'
            ]);

            $modelClass = "App\\Models\\" . $request->model;

            if (!class_exists($modelClass)) {
                return response()->json([
                    'message' => 'Model not found'
                ], 404);
            }

            $record = $modelClass::findOrFail($request->id);

            $record->status = !$record->status;
            $record->save();

            return response()->json([
                'status' => $record->status,
                'message' => 'Status updated successfully'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
