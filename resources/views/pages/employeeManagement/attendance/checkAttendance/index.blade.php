@extends('layouts.master')

@section('title', 'Check Attendance')
@section('mainbreadcrumb', 'Employee Management')
@section('subbreadcrumb', 'Check Attendance')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h4>Employee Attendance Chart</h4>

        <div class="d-flex" style="gap:10px;">
            <select id="yearSelect" class="form-control w-auto"></select>
            <select id="monthSelect" class="form-control">
                <option value="1">Jan</option>
                <option value="2">Feb</option>
                <option value="3">Mar</option>
                <option value="4">Apr</option>
                <option value="5">May</option>
                <option value="6">Jun</option>
                <option value="7">Jul</option>
                <option value="8">Aug</option>
                <option value="9">Sep</option>
                <option value="10">Oct</option>
                <option value="11">Nov</option>
                <option value="12">Dec</option>
            </select>
        </div>
    </div>

<style>
    .employee-header th {
        font-size: 11px;
        text-align: center;
        padding: 0px 0;
        height: 24px !important;
        line-height: 15px;
        vertical-align: middle !important;
    }

    .employee-data td:first-child {
        font-size: 11px;
        text-align: center;
    }

    .calendar-table th,
    .calendar-table td {
        width: 28px;
        height: 20px !important;
        padding: 0 1px;
        line-height: 0px;
    }

    .employee-header {
        background: #0d6efd;
        color: #fff;
        font-weight: bold;
    }

    .past-date {
        background: #e9ecef !important;
        color: #999;
    }

    .past-present {
        background: darkgreen !important;
        color: #fff;
    }

    .past-absent {
        background: darkred !important;
        color: #fff;
    }

    .past-halfday {
        background: #856404 !important;
        color: #fff;
    }

    .past-holiday {
        background: darkblue !important;
        color: #fff;
    }

    .past-overtime {
        background: #4b0082 !important;
        color: #fff;
    }

    .past-weekly_off {
        background: #6c757d !important;
        color: #fff;
    }

    .past-not_marked {
        background: #f8f9fa !important;
        color: #999;
    }

    .bg-present {
        background: #28a745 !important;
        color: #fff;
    }

    .bg-absent {
        background: #dc3545 !important;
        color: #fff;
    }

    .bg-halfday {
        background: #ffc107 !important;
        color: #000;
    }

    .bg-holiday {
        background: #17a2b8 !important;
        color: #fff;
    }

    .bg-overtime {
        background: #6f42c1 !important;
        color: #fff;
    }

    .bg-weekly_off {
        background: #6c757d !important;
        color: #fff;
    }

    .bg-not_joined {
        background: #f8f9fa !important;
        color: #6c757d;
    }

    .past-not_joined {
        background: #e9ecef !important;
        color: #6c757d;
    }

    .calendar-table td {
        border: 1px solid #d6dbdf;
        transition: 0.2s;
    }

    .legend-box {
        width: 18px;
        height: 12px;
        border-radius: 2px;
        display: inline-block;
    }
</style>

<div class="card">
    

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered calendar-table">
                <thead>
                    <tr id="dateRow" class="employee-header">
                        <th>Employee</th>
                    </tr>
                    <tr id="dayRow" class="employee-header">
                        <th>Name</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($employees as $employee)
                    <tr class="employee-data" data-employee="{{ $employee->id }}">
                        <td>{{ $employee->first_name }} </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- color assign -->
        <div class="container">
            <div class="d-flex align-items-center justify-content-center flex-wrap" style="gap: 9px;">

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-present" style="border:1px solid black"></span>
                    Present
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-absent" style="border:1px solid black"></span>
                    Absent
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-halfday" style="border:1px solid black"></span>
                    Half Day
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-holiday" style="border:1px solid black"></span>
                    Holiday
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-overtime" style="border:1px solid black"></span>
                    Overtime
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-weekly_off" style="border:1px solid black"></span>
                    Weekly Off
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box bg-not_joined" style="border:1px solid black"></span>
                    Not Joined
                </div>

                <div class="d-flex align-items-center" style="gap: 3px;">
                    <span class="legend-box past-date" style="border:1px solid black"></span>
                    Past Date
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Pass data to JS --}}
<script>
    const attendances = @json($attendances);
    const holidays = @json($holidays);
    const weeklyOffs = @json($weeklyOffs);
    const employeesData = @json($employeesData);
    const currentYear = {{ $year }};
    const currentMonth = {{ $month }};
</script>

<script>
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');

    // Populate Years
    function populateYears() {
        let currentYear = new Date().getFullYear();
        for (let y = currentYear - 5; y <= currentYear + 5; y++) {
            let opt = new Option(y, y);
            yearSelect.add(opt);
        }
    }

    // Get attendance status
    function getAttendanceStatus(employeeId, dateStr) {
        const date = new Date(dateStr);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // Check holiday
        if (holidays.includes(dateStr)) return 'holiday';

        // Check weekly off
        const woffs = weeklyOffs[employeeId] || [];
        if (woffs.some(w => w.day_of_week == date.getDay())) return 'weekly_off';

        // Check attendance
        const atts = attendances[employeeId] || [];
        const att = atts.find(a => a.formatted_date === dateStr);
        if (att) {
            if (att.status === 'present' && parseFloat(att.overtime_hours) > 0) return 'overtime';
            return att.status; // present, absent, halfday
        }

        // No attendance data
        const employee = employeesData[employeeId];
        if (employee && employee.joining_date) {
            const joiningDate = new Date(employee.joining_date);
            if (date < joiningDate) {
                return 'not_joined';
            } else if (date < today) {  // Only mark absent for past dates
                return 'absent';
            } else {
                return 'not_marked';
            }
        }
        return 'not_marked';
    }

    function generateCalendar() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);

        const dateRow = document.getElementById('dateRow');
        const dayRow = document.getElementById('dayRow');

        dateRow.innerHTML = '<th>Employee</th>';
        dayRow.innerHTML = '<th>Name</th>';

        let days = new Date(year, month, 0).getDate();

        for (let d = 1; d <= days; d++) {
            let date = new Date(year, month - 1, d);

            dateRow.innerHTML += `<th>${d}</th>`;
            dayRow.innerHTML += `<th>${dayNames[date.getDay()]}</th>`;
        }

        updateRows(days, year, month);
    }

    function updateRows(days, year, month) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        document.querySelectorAll('.employee-data').forEach(row => {
            let employeeId = row.dataset.employee;
            let nameCell = row.querySelector('td').textContent;

            row.innerHTML = `<td>${nameCell}</td>`;

            for (let d = 1; d <= days; d++) {
                let dateStr = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                let currentDate = new Date(dateStr);
                currentDate.setHours(0, 0, 0, 0);

                let status = getAttendanceStatus(employeeId, dateStr);
                let className = '';

                if (currentDate < today) {
                    className = status === 'not_marked' ? 'past-date' : `past-${status}`;
                } else {
                    className = `bg-${status}`;
                }

                row.innerHTML += `<td class="${className}" title="${status.replace('_', ' ')}"></td>`;
            }
        });
    }

    // Redirect on change
    function redirectToNewMonth() {
        const url = new URL(window.location);
        url.searchParams.set('year', yearSelect.value);
        url.searchParams.set('month', monthSelect.value);
        window.location.href = url;
    }

    // Init
    populateYears();
    yearSelect.value = currentYear;
    monthSelect.value = currentMonth;

    yearSelect.addEventListener('change', redirectToNewMonth);
    monthSelect.addEventListener('change', redirectToNewMonth);

    generateCalendar();
</script>

@endsection