<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\Middleware;


class DeveloperController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:developers,view', only: ['index', 'show']),
            new Middleware('permission:developers,create', only: ['create', 'store']),
            new Middleware('permission:developers,edit', only: ['edit', 'update']),
            new Middleware('permission:developers,delete', only: ['destroy']),
        ];
    }


    public function index(Request $request)
    {
        // Permissions for filter dropdown
        $permissions = Permission::where('status', 1)->where('is_visible_to_hotel_owner', 0)->get();

        // Base query (only admins)
        $adminsQuery = User::where('user_role_id', 2);

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

        return view('pages.developers.index', compact('admins', 'permissions'));
    }

    public function show(User $developer)
    {
        $data = [
            'title' => 'View Admin Details',

            'fields' => [
                [
                    'label' => 'Developer Name',
                    'value' => $developer->name,
                ],
                [
                    'label' => 'Developer Email',
                    'value' => $developer->email,
                ],
                [
                    'label' => 'Permissions',
                    'value' => $developer->permissions
                        ? collect($developer->permissions)->map(function ($actions, $module) {
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
                'user_role_id'  => 2,
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

    public function destroy(User $developer)
    {
        try {
            $developer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Developer deleted successfully'
            ]);
        } catch (\Throwable $e) {
            Log::error('Developer Delete Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Developer'
            ], 500);
        }
    }

    public function update(Request $request, User $developer)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $developer->id,
            'permissions' => 'nullable|array',
        ]);

        try {
            $developer->update([
                'name'        => $request->name,
                'email'       => $request->email,
                'permissions' => $request->permissions ? $request->permissions : [],
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Developer updated successfully',
                'data'    => $developer
            ]);
        } catch (\Throwable $e) {
            Log::error('Developer Update Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update developer' . $e->getMessage()
            ], 500);
        }
    }
}
