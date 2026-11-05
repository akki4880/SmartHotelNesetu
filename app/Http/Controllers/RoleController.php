<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:roles,view', only: ['index', 'show']),
            new Middleware('permission:roles,create', only: ['create', 'store']),
            new Middleware('permission:roles,edit', only: ['edit', 'update']),
        ];
    }

    public function index(Request $request)
    {
        $query = UserRole::where('hotel_id', Auth::user()->hotel_id);

        if ($request->filled('role_name')) {
            $query->where('role_name', 'like', '%' . $request->role_name . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        $userRoles = $query->orderby('id', 'DESC')->paginate(perPage())->withQueryString();

        $subscription = Subscription::where('hotel_id', auth()->user()->hotel_id)
            ->latest()
            ->first();

        $plan_id = $subscription->plan_id ?? null;

        $rawPermissions = Plan::where('id', $plan_id)->value('permissions') ?? [];
        $permissions = collect($rawPermissions)->map(function ($actions, $key) {
            return (object)[
                'id' => $key, // use key instead (better actually)
                'module_key' => $key,
                'module_name' => ucwords(str_replace('_', ' ', $key)),
                'actions' => $actions,
            ];
        })->values();

        return view('pages.employeeManagement.roles.index', compact('userRoles', 'permissions'));
    }

    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'role_name'   => 'required|string|max:255|unique:user_roles,role_name',
                'description' => 'nullable|string',
            ]);

            UserRole::create([
                'hotel_id'       => Auth::user()->hotel_id,
                'role_name'        => $validated['role_name'],
                'description' => $validated['description'],
                'created_by' => Auth::id(),
                'permissions' => $request->permissions ? $request->permissions : null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Role created successfully'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            \Log::error('Role creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again later.' . $e->getMessage()
            ], 500);
        }
    }


    public function show(UserRole $role)
    {
        // If it's an AJAX request, return JSON

        $data = [
            'title' => 'View UserRole Details',

            'fields' => [
                [
                    'label' => 'Role Name',
                    'value' => $role->role_name,
                ],
                [
                    'label' => 'Description',
                    'value' => $role->description,
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($role->created_at),
                ],
                [
                    'label' => 'Permissions',
                    'value' => $role->permissions
                        ? collect($role->permissions)->map(function ($actions, $module) {
                            return $module . ': ' . implode(' | ', $actions);
                        })->implode(', ')
                        : 'N/A',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    public function edit(UserRole $role)
    {
        if (request()->expectsJson() || request()->wantsJson()) {
            return response()->json([
                'id' => $role->id,
                'role_name' => $role->role_name,
                'description' => $role->description,
                'permissions' => is_array($role->permissions)
        ? $role->permissions
        : json_decode($role->permissions, true)
            ]);
        }
    }

    public function update(Request $request, UserRole $role)
    {
        try {
            $validated = $request->validate([
                'role_name'   => 'required|string|max:255|unique:user_roles,role_name,' . $role->id,
                'description' => 'nullable|string',
            ]);

            $role->update([
                'role_name'    => $validated['role_name'],
                'description'  => $validated['description'],
                'permissions'  => $request->permissions ? $request->permissions : null,
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Role updated successfully'
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {

            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {

            \Log::error('Role update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong. Please try again later.' . $e->getMessage()
            ], 500);
        }
    }
}
