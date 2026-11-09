<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Department;
use App\Models\State;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\Shift;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class EmployeeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:employees,view', only: ['index', 'show', 'birthdayChart']),
            new Middleware('permission:employees,create', only: ['create', 'store']),
            new Middleware('permission:employees,edit', only: ['edit', 'update']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Employee::with('documents', 'department', 'userRole', 'shift')
            ->where('hotel_id', auth()->user()->hotel_id);

        /* Employee Name */
        if ($request->filled('first_name')) {
            $query->where('first_name', 'like', '%' . $request->first_name . '%')
                ->orWhere('last_name', 'like', '%' . $request->first_name . '%');
        }

        /* Phone */
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        /* Email */
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        /* Department Filter */
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        /* Document Type (relation filter) */
        if ($request->filled('document_name')) {
            $query->whereHas('documents', function ($q) use ($request) {
                $q->where('document_name', $request->document_name);
            });
        }

        $employees = $query
            ->latest()
            ->paginate(perPage())
            ->withQueryString();

        $countries = Country::all();
        $departments = Department::where('hotel_id', auth()->user()->hotel_id)->where('status', 1)->get();
        $shifts = Shift::where('hotel_id', auth()->user()->hotel_id)->where('status', 1)->get();
        $roles = UserRole::where('hotel_id', auth()->user()->hotel_id)->where('status', 1)->get();
        return view(
            'pages.employeeManagement.employee.index',
            compact('countries', 'departments', 'shifts', 'employees', 'roles')
        );
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
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('hotel_id', auth()->user()->hotel_id);
                }),
            ],
            'email'     => 'nullable|email|unique:employees|unique:users',
            'address'   => 'nullable|string',
            'dob'       => 'nullable|date',
            'employee_code' => 'required|unique:employees',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'user_role_id' => 'nullable|exists:user_roles,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'salary_amount' => 'required|numeric|min:0',
            'document_name.*'  => 'required_with:document_file.*|string',
            'document_file.*' => 'required_with:document_name.*|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);


        try {
            DB::beginTransaction();

            // 1️⃣ Create Employee
            $employee = Employee::create([
                'hotel_id' => auth()->user()->hotel_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'dob' => $request->dob,
                'employee_code' => $request->employee_code,
                'joining_date' => $request->joining_date,
                'department_id' => $request->department_id,
                'user_role_id' => $request->user_role_id,
                'shift_id' => $request->shift_id,
                'employment_type' => $request->employment_type,
                'salary_type' => $request->salary_type,
                'salary_amount' => $request->salary_amount,
                'status' => $request->status ?? 1,
            ]);

            // 2️⃣ Create User Account
            if ($request->email) {
                User::create([
                    'name' => $request->first_name . ' ' . ($request->last_name ?? ''),
                    'email' => $request->email,
                    'password' => Hash::make($request->phone), // Mobile number as password
                    'user_role_id' => 3, // Employee role
                    'hotel_id' => auth()->user()->hotel_id,
                    'permissions' => null,
                ]);
            }

            // 3️⃣ Save Documents
            if ($request->has('document_name')) {

                foreach ($request->document_name as $index => $type) {

                    if (!$request->hasFile("document_file.$index")) {
                        continue;
                    }

                    // Dynamic folder
                    $folder = 'employee_documents/' . auth()->user()->hotel_id . '/' . $employee->id;

                    $path = $request->file("document_file.$index")
                        ->store($folder, 'public');

                    EmployeeDocument::create([
                        'hotel_id' => auth()->user()->hotel_id,
                        'employee_id' => $employee->id,
                        'document_name' => $type,
                        'file_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Employee created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Employee $employee)
    {
        $data = [
            'title' => 'View Employee Details',

            'fields' => [
                [
                    'label' => 'Employee Code',
                    'value' => $employee->employee_code,
                ],
                [
                    'label' => 'Employee Name',
                    'value' => $employee->first_name . ' ' . ($employee->last_name ?? ''),
                ],
                [
                    'label' => 'Phone',
                    'value' => $employee->phone,
                ],
                [
                    'label' => 'Email',
                    'value' => $employee->email,
                ],
                [
                    'label' => 'Address',
                    'value' => $employee->address,
                ],
                [
                    'label' => 'Date of Birth',
                    'value' => $employee->dob ? formatDateTime($employee->dob, 'd M Y') : 'N/A',
                ],
                [
                    'label' => 'Joining Date',
                    'value' => formatDateTime($employee->joining_date, 'd M Y'),
                ],
                [
                    'label' => 'Department',
                    'value' => $employee->department->name ?? 'N/A',
                ],
                [
                    'label' => 'Shift',
                    'value' => $employee->shift->name ?? 'N/A',
                ],
                [
                    'label' => 'Role',
                    'value' => $employee->userRole->name ?? 'N/A',
                ],
                [
                    'label' => 'Employment Type',
                    'value' => ucfirst($employee->employment_type),
                ],
                [
                    'label' => 'Salary Type',
                    'value' => ucfirst($employee->salary_type),
                ],
                [
                    'label' => 'Salary Amount',
                    'value' => $employee->salary_amount,
                ],
                [
                    'label' => 'Status',
                    'value' => $employee->status ? 'Active' : 'Inactive',
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($employee->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => [
                'required',
                'string',
                'max:20',
                Rule::unique('employees')->where(function ($query) {
                    return $query->where('hotel_id', auth()->user()->hotel_id);
                })->ignore($id),
            ],
            'email'     => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($request->email, 'email'),
                Rule::unique('employees')->ignore($id),
            ],
            'address'   => 'nullable|string',
            'dob'       => 'nullable|date',
            'joining_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'user_role_id' => 'nullable|exists:user_roles,id',
            'shift_id' => 'nullable|exists:shifts,id',
            'employment_type' => 'required|in:full_time,part_time,contract',
            'salary_type' => 'required|in:monthly,daily,hourly',
            'salary_amount' => 'required|numeric|min:0',
            'document_name.*'  => 'required_with:document_file.*|string',
            'document_file.*' => 'nullable|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($id);

            // 1️⃣ Update Employee
            $employee->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone'     => $request->phone,
                'email'     => $request->email,
                'address'   => $request->address,
                'dob'       => $request->dob,
                'joining_date' => $request->joining_date,
                'department_id' => $request->department_id,
                'user_role_id' => $request->user_role_id,
                'shift_id'  => $request->shift_id,
                'employment_type' => $request->employment_type,
                'salary_type' => $request->salary_type,
                'salary_amount' => $request->salary_amount,
                'status' => $request->status ?? 1,
            ]);

            // 2️⃣ Update or Create User Account
            if ($request->email) {
                $user = User::where('email', $request->email)->first();
                if ($user) {
                    // Update existing user
                    $user->update([
                        'name' => $request->first_name . ' ' . ($request->last_name ?? ''),
                        'password' => Hash::make($request->phone), // Update password if phone changed
                    ]);
                } else {
                    // Create new user if email was added
                    User::create([
                        'name' => $request->first_name . ' ' . ($request->last_name ?? ''),
                        'email' => $request->email,
                        'password' => Hash::make($request->phone),
                        'user_role_id' => 3,
                        'hotel_id' => auth()->user()->hotel_id,
                        'permissions' => null,
                    ]);
                }
            }

            // 3️⃣ Add new documents if uploaded
            if ($request->has('document_name')) {

                foreach ($request->document_name as $index => $type) {

                    if (!$request->hasFile("document_file.$index")) {
                        continue;
                    }

                    $folder = 'employee_documents/' . auth()->user()->hotel_id . '/' . $employee->id;

                    $path = $request->file("document_file.$index")
                        ->store($folder, 'public');

                    EmployeeDocument::create([
                        'employee_id' => $employee->id,
                        'document_name' => $type,
                        'file_path' => $path,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Employee updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        //
    }

    public function find(Request $request)
    {
        try {
            $employee = Employee::with('documents')->where('hotel_id', auth()->user()->hotel_id)
                ->where('mobile', $request->mobile)
                ->first();

            return response()->json([
                'status' => true,
                'data'   => $employee
            ]);
        } catch (\Throwable $e) {

            \Log::error('Employee find error', [
                'mobile' => $request->mobile,
                'error'  => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch employee details',
                'error'   => $e->getMessage() // remove in production if needed
            ], 500);
        }
    }

    public function birthdayChart(Request $request)
    {
        $year = $request->year ?? now()->year;
        $month = $request->month ?? now()->month;

        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'month' => 'nullable|integer|min:1|max:12',
        ]);

        $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;

        $employees = Employee::whereNotNull('dob')
            ->where('hotel_id', auth()->user()->hotel_id)
            ->whereMonth('dob', $month)
            ->orderByRaw('DAY(dob) ASC')
            ->orderBy('first_name')
            ->get();

        return view('pages.employeeManagement.employee.birthday-chart.index', compact(
            'employees',
            'year',
            'month',
            'daysInMonth'
        ));
    }
}
