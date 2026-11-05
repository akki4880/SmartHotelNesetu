<?php

namespace App\Http\Controllers;

use App\Models\Action;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:permissions,view', only: ['index', 'show', 'edit']),
            new Middleware('permission:permissions,create', only: ['create', 'store']),
            new Middleware('permission:permissions,edit', only: ['update']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        // Filter
        if ($request->filled('module_name')) {
            $query->where('module_name', 'like', '%' . $request->module_name . '%');
        }
        // 🔹 Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $query->orderBy('id', 'desc');
        $actions = Action::where('status', 1)->get();
        // Pagination
        $permissions = $query->paginate(perPage())->withQueryString();

        return view('pages.permissions.index', compact('permissions', 'actions'));
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
        $validActions = Action::where('status', 1)->pluck('action_name')->toArray();

        $request->validate([
            'module_name' => 'required|string|max:100|unique:permissions,module_name',
            'actions'     => 'required|array|min:1',
            'actions.*'   => Rule::in($validActions),
            'status'      => 'required|boolean',
        ]);

        try {
            $isVisible = $request->is_visible_to_hotel_owner ?? 0;
            $isVisibletosuperadmin = $request->is_visible_to_super_admin ?? 0;

            Permission::create([
                'module_key'  => Str::slug($request->module_name, '_'),
                'module_name' => $request->module_name,
                'actions'     => $request->actions,
                'status'      => $request->status,
                'is_visible_to_hotel_owner' => $isVisible,
                'is_visible_to_super_admin' => $isVisibletosuperadmin
            ]);

            return response()->json([
                'status'  => true,
                'message' => 'Permission created successfully'
            ]);
        } catch (\Exception $e) {

            Log::error('Permission Create Error: ' . $e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create permission'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission)
    {
        // Default: Return HTML for view modal
        $data = [
            'title' => 'View Plan Details',

            'fields' => [
                [
                    'label' => 'Module Name',
                    'value' => $permission->module_name,
                ],
                [
                    'label' => 'Actions',
                    'value' => $permission->actions ? implode(', ', $permission->actions) : 'N/A',
                ],
                [
                    'label' => 'Status',
                    'value' => $permission->status ? 'Active' : 'Inactive',
                ],
                [
                    'label' => 'Visible to Hotel Owner',
                    'value' => $permission->is_visible_to_hotel_owner ? 'Yes' : 'No',
                ],
                [
                    'label' => 'Visible to Super Admin',
                    'value' => $permission->is_visible_to_super_admin ? 'Yes' : 'No',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission)
    {
        return response()->json([
            'id' => $permission->id,
            'module_name' => $permission->module_name,
            'module_key' => $permission->module_key,
            'actions' => $permission->actions ?? [],
            'status' => $permission->status,
            'is_visible_to_hotel_owner' => $permission->is_visible_to_hotel_owner ?? 0,
            'is_visible_to_super_admin' => $permission->is_visible_to_super_admin ?? 0,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        try {
            $validActions = Action::where('status', 1)->pluck('action_name')->toArray();

            $validated = $request->validate([
                'module_name' => 'required|string|max:255|unique:permissions,module_name,' . $permission->id,
                'actions'     => 'required|array|min:1',
                'actions.*'   => Rule::in($validActions),
                'status'      => 'required|boolean',
            ]);

            $isVisible = $request->is_visible_to_hotel_owner ?? 0;
            $isVisibletosuperadmin = $request->is_visible_to_super_admin ?? 0;

            $permission->update([
                'module_name' => $validated['module_name'],
                'module_key'  => \Str::slug($validated['module_name'], '_'),
                'actions'     => $validated['actions'],
                'status'      => $validated['status'],
                'is_visible_to_hotel_owner' => $isVisible,
                'is_visible_to_super_admin' => $isVisibletosuperadmin
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Permission updated successfully'
            ]);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        //
    }

}
