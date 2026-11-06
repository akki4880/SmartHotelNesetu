<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DepartmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:departments,view', only: ['index', 'show']),
            new Middleware('permission:departments,create', only: ['create', 'store']),
            new Middleware('permission:departments,edit', only: ['edit', 'update']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Department::where('hotel_id', Auth::user()->hotel_id)
            ->orderBy('id', 'desc');

        // 🔍 Filters

        // Name
        if (request('name')) {
            $query->where('name', 'like', '%' . request('name') . '%');
        }

        // Status
        if (request('status') !== null && request('status') !== '') {
            $query->where('status', request('status'));
        }

        // Final Data
        $departments = $query->paginate(perPage())->withQueryString();

        return view('pages.employeeManagement.department.index', compact('departments'));
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
            // ✅ Validation
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:departments,name,NULL,id,hotel_id,' . auth()->user()->hotel_id
                ],
                'description' => [
                    'nullable',
                    'string'
                ],
            ]);

            // ✅ Assuming hotel_id comes from logged-in user
            $hotelId = auth()->user()->hotel_id;

            DB::beginTransaction();
            $code = CodeGenerator(
                'inventory_categories',
                'code',
                'DEP-',
                4,
                $hotelId
            );
            // ✅ Create department
            $department = Department::create([
                'hotel_id'   => $hotelId,
                'name'       => $validated['name'],
                'code'       => $code,
                'description' => $validated['description'] ?? null,
                'status'     => 1,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Department created successfully',
                'data' => $department
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // Handle duplicate entry (hotel_id + name unique)
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'Department already exists for this hotel'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Database error occurred'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        $data = [
            'title' => 'View Department Details',

            'fields' => [
                [
                    'label' => 'Name',
                    'value' => $department->name,
                ],
                [
                    'label' => 'Description',
                    'value' => $department->description,
                ],
                [
                    'label' => 'Code',
                    'value' => $department->code,
                ],
                [
                    'label' => 'Status',
                    'value' => $department->status == 1 ? 'Active' : 'Inactive',
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:255',
                    'unique:departments,name,' . $id . ',id,hotel_id,' . auth()->user()->hotel_id
                ],
                'description' => [
                    'nullable',
                    'string'
                ],
            ]);
            // ✅ Find department (ensure it belongs to same hotel)
            $department = Department::where('id', $id)
                ->where('hotel_id', auth()->user()->hotel_id)
                ->firstOrFail();

            // ✅ Validation (ignore unique for current record)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // ✅ Update values
            $department->update([
                'name'        => $validated['name'],
                'status'      => $request->status ?? $department->status,
                'code'        => strtoupper(substr($validated['name'], 0, 3)), // regenerate if needed
                'description' => $validated['description'] ?? null,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Department updated successfully',
                'data' => $department
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();

            // ✅ Handle unique constraint (hotel_id + name)
            if ($e->errorInfo[1] == 1062) {
                return response()->json([
                    'status' => false,
                    'message' => 'Department name already exists for this hotel'
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Database error occurred'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        //
    }

}
