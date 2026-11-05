<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Booking;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\RoomNumber;
use App\Models\Employee;
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:admins,view', only: ['index', 'show']),
            new Middleware('permission:admins,create', only: ['create', 'store']),
            new Middleware('permission:admins,edit', only: ['edit', 'update']),
            new Middleware('permission:admins,delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        // Permissions for filter dropdown
        $permissions = Permission::where('status', 1)->where('is_visible_to_super_admin', 1)->get();

        // Base query (only admins)
        $adminsQuery = User::where('user_role_id', 1);

        // 🔹 Name filter
        if ($request->filled('name')) {
            $adminsQuery->where('name', 'like', '%' . $request->name . '%');
        }

        // 🔹 Email filter
        if ($request->filled('email')) {
            $adminsQuery->where('email', 'like', '%' . $request->email . '%');
        }

        // ✅ Pagination + keep filters
        $admins = $adminsQuery->orderby('id', 'DESC')->paginate(perPage())->withQueryString();

        return view('pages.admins.index', compact('admins', 'permissions'));
    }

    public function show(User $admin)
    {
        $data = [
            'title' => 'View Admin Details',

            'fields' => [
                [
                    'label' => 'Admin Name',
                    'value' => $admin->name,
                ],
                [
                    'label' => 'Admin Email',
                    'value' => $admin->email,
                ],
                [
                    'label' => 'Permissions',
                    'value' => $admin->permissions
                        ? collect($admin->permissions)->map(function ($actions, $module) {
                            return $module . ': ' . implode('|', $actions);
                        })->implode(', ')
                        : 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|string|min:6|confirmed',
            'user_role_id'          => 'required|integer',
            'permissions'           => 'nullable|array',
        ]);

        try {
            $permissions = $request->permissions ?? [];

            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'password'      => Hash::make($request->password),
                'user_role_id'  => $request->user_role_id,
                'permissions'   => $permissions,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Admin created successfully',
                'data'    => $user
            ]);
        } catch (\Throwable $e) {

            Log::error('Admin Create Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create admin' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(User $admin)
    {
        try {
            $admin->delete();

            return response()->json([
                'status' => true,
                'message' => 'Admin deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin Delete Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete admin'
            ], 500);
        }
    }

    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $admin->id,
            'permissions' => 'nullable|array',
        ]);

        try {
            $admin->update([
                'name'        => $request->name,
                'email'       => $request->email,
                'permissions' => $request->permissions ? $request->permissions : [],
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Admin updated successfully',
                'data'    => $admin
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin Update Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update admin' . $e->getMessage()
            ], 500);
        }
    }

}
