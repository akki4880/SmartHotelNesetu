<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Shift;
use App\Models\WeeklyOff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AttendanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:attendance_records,view', only: ['index', 'show']),
            new Middleware('permission:attendance_records,create', only: ['create', 'store']),
            new Middleware('permission:attendance_records,delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Attendance::with(['employee', 'shift', 'hotel']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('attendance_date')) {
            $query->where('attendance_date', $request->attendance_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderby('attendance_date', 'DESC')->paginate(perPage())->withQueryString();

        $employees = Employee::all();
        $shifts = Shift::all();

        return view('pages.employeeManagement.attendance.index', compact('attendances', 'employees', 'shifts'));
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
        // ✅ Step 1: Validation
        $request->validate([
            'employee_id'     => 'required|exists:employees,id',
            'attendance_date' => 'required|date',
            'check_in'        => 'nullable|date_format:H:i',
            'check_out'       => 'nullable|date_format:H:i|after_or_equal:check_in',
            'remarks'         => 'nullable|string|max:500',
        ]);

        $hotelId = auth()->user()->hotel_id;
        $checkInDateTime = $request->filled('check_in')
            ? Carbon::createFromFormat('Y-m-d H:i', $request->attendance_date . ' ' . $request->check_in)
            : null;
        $checkOutDateTime = $request->filled('check_out')
            ? Carbon::createFromFormat('Y-m-d H:i', $request->attendance_date . ' ' . $request->check_out)
            : null;

        // ✅ Step 2: Prevent duplicate manually (better error)
        $exists = Attendance::where('employee_id', $request->employee_id)
            ->where('attendance_date', $request->attendance_date)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'attendance_date' => 'Attendance already marked for this date.'
            ]);
        }

        // ✅ Step 3: Fetch employee + shift
        $employee = Employee::with('shift')->findOrFail($request->employee_id);
        $shift = $employee->shift;

        $date = Carbon::parse($request->attendance_date);
        $dayName = strtolower($date->format('l'));

        // ✅ Step 4: Default values
        $status = 'present';
        $lateMinutes = 0;
        $workingHours = 0;
        $overtime = 0;

        // ✅ Step 5: Check Holiday
        $isHoliday = Holiday::where('hotel_id', $hotelId)
            ->where('date', $request->attendance_date)
            ->exists();

        if ($isHoliday) {
            $status = 'holiday';
        }

        // ✅ Step 6: Check Weekly Off
        $isWeekOff = WeeklyOff::where('employee_id', $employee->id)
            ->where('day_of_week', $dayName)
            ->exists();

        if ($isWeekOff) {
            $status = 'week_off';
        }

        // ✅ Step 7: If Check-in exists → calculate timing
        if ($checkInDateTime && $shift) {
            $shiftStart = Carbon::parse($request->attendance_date . ' ' . $shift->start_time);
            $shiftEnd = Carbon::parse($request->attendance_date . ' ' . $shift->end_time);

            if ($shift->end_time < $shift->start_time) {
                $shiftEnd->addDay();
            }

            $grace = $shift->grace_minutes ?? 0;

            if ($checkInDateTime->gt($shiftStart->copy()->addMinutes($grace))) {
                $lateMinutes = $shiftStart->diffInMinutes($checkInDateTime);
                $status = 'late';
            }
        }

        // ✅ Step 8: Working Hours Calculation
        if ($checkInDateTime && $checkOutDateTime) {
            $workingMinutes = $checkOutDateTime->diffInMinutes($checkInDateTime);

            if ($checkOutDateTime->lt($checkInDateTime)) {
                $workingMinutes = $checkOutDateTime->copy()->addDay()->diffInMinutes($checkInDateTime);
            }

            $workingHours = $workingMinutes / 60;

            // Assume 8 hrs standard
            if ($workingHours > 8) {
                $overtime = $workingHours - 8;
            }

            // Half Day logic
            if ($workingHours < 4) {
                $status = 'half_day';
            }
        }

        // ✅ Step 9: If no check-in → absent (but not override holiday/weekoff)
        if (!$request->check_in && !in_array($status, ['holiday', 'week_off'])) {
            $status = 'absent';
        }

        // ✅ Step 10: Store Attendance
        Attendance::create([
            'hotel_id'        => $hotelId,
            'employee_id'     => $employee->id,
            'shift_id'        => $shift?->id,
            'attendance_date' => $request->attendance_date,
            'check_in'        => $checkInDateTime?->format('Y-m-d H:i:s'),
            'check_out'       => $checkOutDateTime?->format('Y-m-d H:i:s'),
            'working_hours'   => $workingHours,
            'overtime_hours'  => $overtime,
            'status'          => $status,
            'late_minutes'    => $lateMinutes,
            'remarks'         => $request->remarks,
        ]);

        return redirect()->back()->with('success', 'Attendance marked successfully!');
    } 

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        $data = [
            'title' => 'View Attendance Details',
            'fields' => [
                [
                    'label' => 'Employee',
                    'value' => $attendance->employee->name ?? 'N/A',
                ],
                [
                    'label' => 'Shift',
                    'value' => $attendance->shift->name ?? 'N/A',
                ],
                [
                    'label' => 'Attendance Date',
                    'value' => $attendance->attendance_date->format('Y-m-d'),
                ],
                [
                    'label' => 'Check In',
                    'value' => $attendance->check_in ? $attendance->check_in->format('H:i') : 'N/A',
                ],
                [
                    'label' => 'Check Out',
                    'value' => $attendance->check_out ? $attendance->check_out->format('H:i') : 'N/A',
                ],
                [
                    'label' => 'Working Hours',
                    'value' => $attendance->working_hours,
                ],
                [
                    'label' => 'Overtime Hours',
                    'value' => $attendance->overtime_hours,
                ],
                [
                    'label' => 'Status',
                    'value' => ucfirst($attendance->status),
                ],
                [
                    'label' => 'Late Minutes',
                    'value' => $attendance->late_minutes,
                ],
                [
                    'label' => 'Remarks',
                    'value' => $attendance->remarks ?? 'N/A',
                ],
                [
                    'label' => 'Created At',
                    'value' => formatDateTime($attendance->created_at),
                ],
            ],
            'table' => null,
        ];

        return view('layouts.dynamic-view-content', compact('data'))->render();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        return response()->json($attendance);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {
        try {
            $validated = $request->validate([
                'employee_id' => 'required|exists:employees,id',
                'shift_id' => 'nullable|exists:shifts,id',
                'attendance_date' => 'required|date',
                'check_in' => 'nullable|date_format:H:i',
                'check_out' => 'nullable|date_format:H:i|after_or_equal:check_in',
                'working_hours' => 'nullable|numeric|min:0',
                'overtime_hours' => 'nullable|numeric|min:0',
                'status' => 'required|in:present,absent,late,half_day,leave,week_off,holiday',
                'late_minutes' => 'nullable|integer|min:0',
                'remarks' => 'nullable|string',
            ]);

            $checkInValue = $request->filled('check_in')
                ? Carbon::createFromFormat('Y-m-d H:i', $request->attendance_date . ' ' . $request->check_in)->format('Y-m-d H:i:s')
                : null;
            $checkOutValue = $request->filled('check_out')
                ? Carbon::createFromFormat('Y-m-d H:i', $request->attendance_date . ' ' . $request->check_out)->format('Y-m-d H:i:s')
                : null;

            $attendance->update(array_merge($validated, [
                'check_in' => $checkInValue,
                'check_out' => $checkOutValue,
            ]));

            return response()->json([
                'status' => true,
                'message' => 'Attendance updated successfully'
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
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json([
            'status' => true,
            'message' => 'Attendance deleted successfully'
        ]);
    }

    // Custom method to check attendances
    public function checkAttendances(Request $request){
        $employees = Employee::all();
        $shifts = Shift::all();

        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $attendances = Attendance::whereBetween('attendance_date', [$startDate, $endDate])
            ->with(['employee', 'shift'])
            ->get()
            ->map(function($att) {
                $att->formatted_date = $att->attendance_date->format('Y-m-d');
                return $att;
            })
            ->groupBy('employee_id')
            ->toArray();

        $holidays = Holiday::whereBetween('date', [$startDate, $endDate])->get()->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();
        $weeklyOffs = WeeklyOff::with('employee')->get()->groupBy('employee_id')->toArray();

        $employeesData = $employees->keyBy('id')->toArray();

        return view('pages.employeeManagement.attendance.checkAttendance.index', compact('employees', 'shifts', 'attendances', 'holidays', 'weeklyOffs', 'year', 'month', 'employeesData'));
    }
}
