@extends('layouts.master')

@section('title', 'Birthday Month Chart')
@section('mainbreadcrumb', 'Employee Management')
@section('subbreadcrumb', 'Birthday Month Chart')

@section('content')

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">

        <h4>Employee Birthday Month Chart</h4>

        <div class="d-flex" style="gap:10px;">

            <select id="yearSelect" class="form-control w-auto">
                @for($y = now()->year - 5; $y <= now()->year + 5; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                    @endfor
            </select>

            <select id="monthSelect" class="form-control">
                @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ Carbon\Carbon::create()->month($m)->format('F') }}
                </option>
                @endforeach
            </select>

        </div>

    </div>

    <style>
        .calendar-table th,
        .calendar-table td {
            min-width: 32px;
            height: 32px !important;
            padding: 3px;
            text-align: center;
            vertical-align: middle !important;
        }

        .calendar-table th:first-child,
        .calendar-table td:first-child {
            min-width: 160px;
            text-align: left;
            padding-left: 10px;
            white-space: nowrap;
        }

        .employee-header {
            background: #0d6efd;
            color: #fff;
            font-weight: bold;
        }

        .birthday-cell {
            background: #ff69b4 !important;
            color: #fff !important;
            font-weight: bold;
            font-size: 16px;
        }

        .normal-cell {
            background: #f8f9fa;
        }

        .legend-box {
            width: 18px;
            height: 12px;
            border-radius: 2px;
            display: inline-block;
        }

        .birthday-legend {
            background: #ff69b4;
        }

        .calendar-table td {
            border: 1px solid #d6dbdf;
        }
    </style>

    <div class="card-body">

        <div class="mb-3">
            <h5>
                Birthdays - {{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h5>

            <span class="text-muted">
                Total Birthdays: {{ $employees->count() }}
            </span>
        </div>

        <div class="table-responsive">

            <table class="table table-bordered calendar-table">

                <thead>

                    <tr class="employee-header">
                        <th>Employee</th>

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <th>{{ $day }}</th>
                            @endfor
                    </tr>

                    <tr class="employee-header">
                        <th>Name</th>

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            <th>
                            {{ Carbon\Carbon::create($year, $month, $day)->format('D') }}
                            </th>
                            @endfor
                    </tr>

                </thead>

                <tbody>

                    @forelse($employees as $employee)

                    @php
                    $birthdayDay = Carbon\Carbon::parse($employee->dob)->day;
                    @endphp

                    <tr class="employee-data">

                        <td>
                            <div class="d-flex flex-column">
                                <span class="font-weight-bold">
                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                </span>

                                <small class="text-muted">
                                    {{ $employee?->department?->name ?? 'No Department' }}
                                </small>
                            </div>
                        </td>

                        @for($day = 1; $day <= $daysInMonth; $day++)

                            @if($birthdayDay==$day)

                            <td class="birthday-cell"
                            title="Birthday: {{ $employee->first_name }}">
                            <i class="fas fa-birthday-cake"></i>
                            </td>

                            @else

                            <td class="normal-cell"></td>

                            @endif

                            @endfor

                    </tr>

                    @empty

                    <tr>
                        <td colspan="{{ $daysInMonth + 1 }}"
                            class="text-center text-muted">
                            No employee birthdays found for this month.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Legend --}}

        <div class="d-flex align-items-center justify-content-center mt-3"
            style="gap: 8px;">

            <span class="legend-box birthday-legend"
                style="border:1px solid black"></span>

            <span>Birthday</span>

        </div>

    </div>

</div>

<script>
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');

    function redirectToNewMonth() {

        const url = new URL(window.location.href);

        url.searchParams.set('year', yearSelect.value);
        url.searchParams.set('month', monthSelect.value);

        window.location.href = url.toString();
    }

    yearSelect.addEventListener('change', redirectToNewMonth);
    monthSelect.addEventListener('change', redirectToNewMonth);
</script>

@endsection