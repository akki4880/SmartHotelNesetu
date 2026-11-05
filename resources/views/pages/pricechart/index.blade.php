@extends('layouts.master')

@section('title', 'Price Chart')

@section('content')
<style>
    .calendar-table {
        width: 100%;
        table-layout: auto;
        /* ✅ IMPORTANT */
        font-size: 11px;
    }


    .calendar-table th,
    .calendar-table td {
        text-align: center;
        padding: 4px;
        border: 1px solid #dee2e6;
    }

    .room-header {
        background: #0d6efd;
        color: #fff;
        font-weight: bold;
    }

    .available {
        background: #00bcd4;
        color: #fff;
    }

    .price {
        background: #20c997;
        color: #fff;
    }

    .legend-box {
        width: 18px;
        height: 12px;
        border-radius: 2px;
        display: inline-block;
    }

    /* Price background colors */
    .price-weekday {
        /* background-color: #e8f5e9; */
        /* soft green */
    }

    .price-weekend {
        background-color: #886ab5 !important;
        color: white;
    }

    .price-seasonal {
        background-color: #fd3995 !important;
        color: white;
    }

    /* Optional: make prices stand out */
    .calendar-table td {
        text-align: center;
        font-weight: 600;
    }
</style>
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header d-flex justify-content-between align-items-center p-2">
                <h3 class="mb-0 fw-bolder"> Price Chart</h3>
                <div class="d-flex justify-content-between" style="width:350px;">
                    <div style="width:165px;">
                        <div class="form-group">
                            <select class="form-control" id="yearSelect">
                                <!-- <option value="2024">2024</option>
                                <option value="2025" selected>2025</option>
                                <option value="2026">2026</option> -->
                            </select>
                        </div>
                    </div>
                    <div style="width:165px;">
                        <div class="form-group">
                            <select class="form-control" id="monthSelect">
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12" selected>December</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="calendar-table table table-bordered">
                        <thead>
                            <tr id="dateRow" class="room-header">
                                <th>Date</th>
                            </tr>
                            <tr id="dayRow" class="room-header">
                                <th>Days</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($roomTypes as $roomType)
                            <tr>
                                <td colspan="32" class="bg-dark text-light">
                                    {{ strtoupper($roomType->name) }} – TOTAL ROOMS ({{ $roomType->total_rooms }})
                                </td>
                            </tr>

                            <tr class="room-data" data-label="AVAILABLE" data-room-type-id="{{ $roomType->id }}">
                                <td class="available">AVAILABLE</td>
                            </tr>

                            <tr class="room-data" data-label="PRICE" data-room-type-id="{{ $roomType->id }}">
                                <td class="price">PRICE</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <!-- color assign -->
                <div class="container">
                    <div class="d-flex align-items-center justify-content-center flex-wrap" style="gap: 9px;">

                        <div class="d-flex align-items-center" style="gap: 3px;">
                            <span class="legend-box bg-primary" style="border:1px solid black"></span>
                            Weekend Rate
                        </div>

                        <div class="d-flex align-items-center" style="gap: 3px;">
                            <span class="legend-box bg-light" style="border:1px solid black"></span>
                            Weekday Rate
                        </div>

                        <div class="d-flex align-items-center" style="gap: 3px;">
                            <span class="legend-box bg-danger" style="border:1px solid black"></span>
                            Seasonal Rate
                        </div>

                        <!-- <div class="d-flex align-items-center" style="gap: 3px;">
                            <span class="legend-box bg-primary"></span>
                            Room Reserved
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');

    // Populate year select dynamically (past 10 years to next 10 years)
    function populateYears() {
        const currentYear = new Date().getFullYear();
        const pastYears = 10;
        const futureYears = 10;

        for (let y = currentYear - pastYears; y <= currentYear + futureYears; y++) {
            const option = document.createElement('option');
            option.value = y;
            option.textContent = y;
            if (y === currentYear) option.selected = true;
            yearSelect.appendChild(option);
        }
    }

    // Generate the calendar table headers
    function generateCalendar(year, month) {
        const dateRow = document.getElementById('dateRow');
        const dayRow = document.getElementById('dayRow');

        dateRow.innerHTML = '<th>Date</th>';
        dayRow.innerHTML = '<th>Days</th>';

        const daysInMonth = new Date(year, month, 0).getDate();

        for (let d = 1; d <= daysInMonth; d++) {
            const date = new Date(year, month - 1, d);
            dateRow.innerHTML += `<th>${d}</th>`;
            dayRow.innerHTML += `<th>${dayNames[date.getDay()]}</th>`;
        }

        updateRoomRows(daysInMonth);
    }

    // Clear and prepare room rows
    function updateRoomRows(daysInMonth) {
        document.querySelectorAll('.room-data').forEach(row => {
            const label = row.dataset.label;
            row.innerHTML = `<td class="${label.toLowerCase()}">${label}</td>`;
            for (let d = 1; d <= daysInMonth; d++) {
                row.innerHTML += `<td></td>`;
            }
        });
    }

    // Fetch room prices/availability from backend
    async function fetchPrices(year, month) {
        const res = await fetch(`/pricechart/getroomrates?year=${year}&month=${month}`);
        return await res.json();
    }

    // Refresh calendar data
    async function refreshCalendar() {
        const year = parseInt(yearSelect.value);
        const month = parseInt(monthSelect.value);

        generateCalendar(year, month);

        const prices = await fetchPrices(year, month);
        const daysInMonth = new Date(year, month, 0).getDate();

        // Update AVAILABLE row
        document.querySelectorAll('.room-data[data-label="AVAILABLE"]').forEach(row => {
            const roomTypeId = row.dataset.roomTypeId;
            for (let d = 1; d <= daysInMonth; d++) {
                const date = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const cell = row.children[d];
                const data = prices?.[roomTypeId]?.[date];

                if (!data) {
                    cell.innerText = '-';
                    cell.className = '';
                    continue;
                }

                cell.innerText = data.available;

                // Set background color based on availability
                // cell.classList.remove('bg-success', 'bg-warning', 'bg-danger', 'text-white');
                // if (data.available === 0) cell.classList.add('bg-danger', 'text-white');
                // else if (data.available <= 2) cell.classList.add('bg-warning');
                // else cell.classList.add('bg-success', 'text-white');
            }
        });

        // Update PRICE row
        document.querySelectorAll('.room-data[data-label="PRICE"]').forEach(row => {
            const roomTypeId = row.dataset.roomTypeId;
            for (let d = 1; d <= daysInMonth; d++) {
                const date = `${year}-${String(month).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const cell = row.children[d];
                const data = prices?.[roomTypeId]?.[date];

                // Reset cell
                cell.innerText = '-';
                cell.classList.remove('price-weekday', 'price-weekend', 'price-seasonal', 'price-hourly');

                if (!data || data.price === null) continue;

                cell.innerText = data.price;

                // Color by type
                if (data.type === 'weekday') cell.classList.add('price-weekday');
                if (data.type === 'weekend') cell.classList.add('price-weekend');
                if (data.type === 'seasonal') cell.classList.add('price-seasonal');
                if (data.type === 'hourly') cell.classList.add('price-hourly');
            }
        });
    }

    // Initial setup
    populateYears();
    yearSelect.value = new Date().getFullYear();
    monthSelect.value = new Date().getMonth() + 1;

    yearSelect.addEventListener('change', refreshCalendar);
    monthSelect.addEventListener('change', refreshCalendar);

    refreshCalendar();
</script>



@endsection