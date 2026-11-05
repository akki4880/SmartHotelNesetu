<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserRoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:user_role,view', only: ['index', 'show']),
            new Middleware('permission:user_role,create', only: ['create', 'store']),
            new Middleware('permission:user_role,edit', only: ['edit', 'update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserRole::where('hotel_id', null);

        if ($request->filled('role_name')) {
            $query->where('role_name', 'like', '%' . $request->role_name . '%');
        }

        if ($request->filled('description')) {
            $query->where('description', 'like', '%' . $request->description . '%');
        }

        $userRoles = $query->orderby('id', 'DESC')->paginate(perPage())->withQueryString();

        return view('pages.userroles.index', compact('userRoles'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'role_name'   => 'required|string|max:255|unique:user_roles,role_name',
                'description' => 'required|string',
            ]);

            UserRole::create([
                'role_name'        => $validated['role_name'],
                'description' => $validated['description'],
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

    /**
     * Display the specified resource.
     */
    public function show(UserRole $userrole)
    {
        $data = [
            'title' => 'View UserRole Details',

            'fields' => [
                [
                    'label' => 'Role Name',
                    'value' => $userrole->role_name,
                ],
                [
                    'label' => 'Description',
                    'value' => $userrole->description,
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($userrole->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserRole $userRole)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserRole $userrole)
    {
        // dd($request->all());
        try {
            $validated = $request->validate([
                'role_name'   => 'required|string|max:255|unique:user_roles,role_name,' . $userrole->id,
                'description' => 'required|string',
            ]);

            $userrole->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Role updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserRole $userRole)
    {
        //
    }
}
