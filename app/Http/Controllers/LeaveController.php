<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LeaveController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:leaves,view', only: ['index', 'show']),
            new Middleware('permission:leaves,create', only: ['create', 'store']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Leave::with('employee', 'approver')
            ->where('hotel_id', auth()->user()->hotel_id);

        /* Employee Filter */
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        /* Leave Type Filter */
        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        /* Status Filter */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /* Date Range Filter */
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('from_date', [$request->from_date, $request->to_date])
                  ->orWhereBetween('to_date', [$request->from_date, $request->to_date]);
        }

        $leaves = $query
            ->latest()
            ->paginate(perPage())
            ->withQueryString();

        $employees = Employee::where('hotel_id', auth()->user()->hotel_id)->where('status', 1)->get();

        return view(
            'pages.employeeManagement.leaves.index',
            compact('employees', 'leaves')
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
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|in:sick,casual,paid,emergency',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total days
            $fromDate = \Carbon\Carbon::parse($request->from_date);
            $toDate = \Carbon\Carbon::parse($request->to_date);
            $totalDays = $fromDate->diffInDays($toDate) + 1;

            // Create Leave
            $leave = Leave::create([
                'hotel_id' => auth()->user()->hotel_id,
                'employee_id' => $request->employee_id,
                'leave_type' => $request->leave_type,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                'status' => 'pending',
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Leave application submitted successfully'
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
    public function show($id)
    {

        $leave = Leave::with(['employee', 'approver'])->findOrFail($id);
        $data = [
            'title' => 'View Leave Details',

            'fields' => [
                [
                    'label' => 'Employee Name',
                    'value' => $leave->employee->first_name . ' ' . ($leave->employee->last_name ?? ''),
                ],
                [
                    'label' => 'Employee Code',
                    'value' => $leave->employee->employee_code,
                ],
                [
                    'label' => 'Leave Type',
                    'value' => ucfirst($leave->leave_type),
                ],
                [
                    'label' => 'From Date',
                    'value' => formatDateTime($leave->from_date, 'd M Y'),
                ],
                [
                    'label' => 'To Date',
                    'value' => formatDateTime($leave->to_date, 'd M Y'),
                ],
                [
                    'label' => 'Total Days',
                    'value' => $leave->total_days,
                ],
                [
                    'label' => 'Reason',
                    'value' => $leave->reason ?? '-',
                ],
                [
                    'label' => 'Status',
                    'value' => ucfirst($leave->status),
                ],
                [
                    'label' => 'Approved By',
                    'value' => $leave->approver ? $leave->approver->name : '-',
                ],
                [
                    'label' => 'Approved At',
                    'value' => $leave->approved_at ? formatDateTime($leave->approved_at) : '-',
                ],
                [
                    'label' => 'Admin Remark',
                    'value' => $leave->admin_remark ?? '-',
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($leave->created_at),
                ],
            ],

            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $leave = Leave::findOrFail($id);
        
        return response()->json([
            'id' => $leave->id,
            'employee_id' => $leave->employee_id,
            'leave_type' => $leave->leave_type,
            'from_date' => $leave->from_date,
            'to_date' => $leave->to_date,
            'reason' => $leave->reason,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type' => 'required|string|in:sick,casual,paid,emergency',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $leave = Leave::findOrFail($id);

            // Calculate total days
            $fromDate = \Carbon\Carbon::parse($request->from_date);
            $toDate = \Carbon\Carbon::parse($request->to_date);
            $totalDays = $fromDate->diffInDays($toDate) + 1;

            // Update Leave
            $leave->update([
                'employee_id' => $request->employee_id,
                'leave_type' => $request->leave_type,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'total_days' => $totalDays,
                'reason' => $request->reason,
                // Reset approval status if dates changed
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'admin_remark' => null,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Leave updated successfully'
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
    public function destroy($id)
    {
        try {
            $leave = Leave::findOrFail($id);
            $leave->delete();

            return response()->json([
                'status' => true,
                'message' => 'Leave deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve leave
     */
    public function approve($id)
    {
        try {
            $leave = Leave::findOrFail($id);
            $leave->update([
                'status' => 'approved',
                'approved_by' => auth()->user()->id,
                'approved_at' => now(),
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Leave approved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject leave
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'admin_remark' => 'required|string|max:1000',
        ]);

        try {
            $leave = Leave::findOrFail($id);
            $leave->update([
                'status' => 'rejected',
                'approved_by' => auth()->user()->id,
                'approved_at' => now(),
                'admin_remark' => $request->admin_remark,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Leave rejected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
