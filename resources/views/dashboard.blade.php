@extends('layouts.master')

@section('title', 'Welcome')

@section('content') 
<!-- ====== STAT CARDS ROW ====== -->
<div class="row gy-3  ">

    <!-- 1. New Booking -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card blue">
            <div class="stat-icon" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="bi bi-calendar-plus"></i></div>
            <div class="stat-value">{{ $stats['new_bookings_today'] }}</div>
            <div class="stat-label">New Bookings Today</div>
            <span class="stat-badge up"><i class="bi bi-arrow-up-short"></i> {{ $stats['new_bookings_today'] }} booked today</span>
        </div>
    </div>

    <!-- 2. Schedule Room -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card purple">
            <div class="stat-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6"><i class="bi bi-calendar3-range"></i></div>
            <div class="stat-value">{{ $stats['rooms_scheduled'] }}</div>
            <div class="stat-label">Rooms Scheduled</div>
            <span class="stat-badge neu"><i class="bi bi-dash"></i> Arrivals today</span>
        </div>
    </div>

    <!-- 3. Total Customers -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card green">
            <div class="stat-icon" style="background:rgba(16,185,129,.12);color:#10b981"><i class="bi bi-people"></i></div>
            <div class="stat-value">{{ number_format($stats['total_customers']) }}</div>
            <div class="stat-label">Total Customers</div>
            <span class="stat-badge up"><i class="bi bi-arrow-up-short"></i> {{ number_format($stats['total_customers']) }} total guests</span>
        </div>
    </div>

    <!-- 4. Total Revenue -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card orange">
            <div class="stat-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="bi bi-currency-rupee"></i></div>
            <div class="stat-value">₹{{ number_format($stats['revenue_this_month'], 2) }}</div>
            <div class="stat-label">Revenue This Month</div>
            <span class="stat-badge up"><i class="bi bi-arrow-up-short"></i> {{ number_format($stats['revenue_this_month'], 2) }} booked</span>
        </div>
    </div>

    <!-- 5. Available Rooms Today -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card cyan">
            <div class="stat-icon" style="background:rgba(6,182,212,.12);color:#06b6d4"><i class="bi bi-door-closed"></i></div>
            <div class="stat-value">{{ $stats['available_rooms'] }}</div>
            <div class="stat-label">Available Rooms</div>
            <span class="stat-badge neu"><i class="bi bi-dash"></i> Out of {{ $totalRooms }} total</span>
        </div>
    </div>

    <!-- 6. Occupied Rooms -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card red">
            <div class="stat-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="bi bi-door-open"></i></div>
            <div class="stat-value">{{ $stats['occupied_rooms'] }}</div>
            <div class="stat-label">Occupied Rooms</div>
            <span class="stat-badge up"><i class="bi bi-arrow-up-short"></i> {{ $occupancyRate }}% occupancy</span>
        </div>
    </div>

    <!-- 7. Check-ins Today -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card pink">
            <div class="stat-icon" style="background:rgba(236,72,153,.12);color:#ec4899"><i class="bi bi-box-arrow-in-right"></i></div>
            <div class="stat-value">{{ $stats['check_ins_today'] }}</div>
            <div class="stat-label">Check-ins Today</div>
            <span class="stat-badge up"><i class="bi bi-arrow-up-short"></i> Based on today</span>
        </div>
    </div>

    <!-- 8. Check-outs Today -->
    <div class="col-6 col-md-3 mb-3">
        <div class="stat-card teal">
            <div class="stat-icon" style="background:rgba(20,184,166,.12);color:#14b8a6"><i class="bi bi-box-arrow-right"></i></div>
            <div class="stat-value">{{ $stats['check_outs_today'] }}</div>
            <div class="stat-label">Check-outs Today</div>
            <span class="stat-badge down"><i class="bi bi-arrow-down-short"></i> Based on today</span>
        </div>
    </div>
</div>

<!-- ====== ROW: Revenue + Room Availability ====== -->
<div class="row g-3 mb-4">

    <!-- Revenue Analytics -->
    <div class="col-lg-7">
        <div class="sh-card mb-4">
            <div class="card-head">
                <h6><i class="bi bi-bar-chart-line"></i> Revenue Analytics</h6>
                <div class="card-actions">
                    <div class="dropdown">
                        <button class="btn-sh btn-sh-ghost btn-sh-sm dropdown-toggle" data-bs-toggle="dropdown">This Month</button>
                        <ul class="dropdown-menu dropdown-menu-end" style="font-size:.82rem">
                            <li><a class="dropdown-item" href="#">This Week</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item" href="#">This Quarter</a></li>
                            <li><a class="dropdown-item" href="#">This Year</a></li>
                        </ul>
                    </div>
                    <button class="btn-sh btn-sh-ghost btn-sh-sm" data-refresh><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
            <div class="card-body-pad">
                <!-- Revenue Summary -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-4">
                        <div style="padding:.875rem;background:var(--sh-bg);border-radius:10px">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Room Revenue</div>
                            <div style="font-size:1.25rem;font-weight:800;color:var(--sh-text)">₹2,84,500</div>
                            <div style="font-size:.72rem;color:#10b981;font-weight:600"><i class="bi bi-arrow-up-short"></i> +18% vs last month</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="padding:.875rem;background:var(--sh-bg);border-radius:10px">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">F&amp;B Revenue</div>
                            <div style="font-size:1.25rem;font-weight:800;color:var(--sh-text)">₹96,300</div>
                            <div style="font-size:.72rem;color:#10b981;font-weight:600"><i class="bi bi-arrow-up-short"></i> +31% vs last month</div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div style="padding:.875rem;background:var(--sh-bg);border-radius:10px">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Other Revenue</div>
                            <div style="font-size:1.25rem;font-weight:800;color:var(--sh-text)">₹39,200</div>
                            <div style="font-size:.72rem;color:#ef4444;font-weight:600"><i class="bi bi-arrow-down-short"></i> -4% vs last month</div>
                        </div>
                    </div>
                </div>
                <!-- Bar Chart -->
                <div class="rev-bar-wrap">
                    <div class="rev-bar" style="height:45%" data-val="85,000"></div>
                    <div class="rev-bar" style="height:62%" data-val="1,10,000"></div>
                    <div class="rev-bar" style="height:55%" data-val="98,500"></div>
                    <div class="rev-bar" style="height:78%" data-val="1,40,000"></div>
                    <div class="rev-bar" style="height:48%" data-val="88,000"></div>
                    <div class="rev-bar" style="height:65%" data-val="1,18,500"></div>
                    <div class="rev-bar" style="height:72%" data-val="1,30,000"></div>
                    <div class="rev-bar active" style="height:88%" data-val="1,58,000"></div>
                    <div class="rev-bar" style="height:80%" data-val="1,44,500"></div>
                    <div class="rev-bar" style="height:70%" data-val="1,26,000"></div>
                    <div class="rev-bar" style="height:60%" data-val="1,08,000"></div>
                    <div class="rev-bar" style="height:92%" data-val="1,65,000"></div>
                </div>
                <div class="rev-labels">
                    <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span>
                    <span>May</span><span>Jun</span><span>Jul</span><span>Aug</span>
                    <span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
                </div>
                <div class="rev-legend mt-3">
                    <div class="rev-legend-item">
                        <div class="rev-legend-dot" style="background:var(--sh-primary)"></div> Room Revenue
                    </div>
                    <div class="rev-legend-item">
                        <div class="rev-legend-dot" style="background:#10b981"></div> F&amp;B Revenue
                    </div>
                    <div class="rev-legend-item">
                        <div class="rev-legend-dot" style="background:#f59e0b"></div> Other
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Availability -->
    <div class="col-lg-5">
        <!-- QUick  -->
        <div class="sh-card">
            <div class="card-head">
                <h6><i class="bi bi-lightning-charge"></i> Quick Actions</h6>
            </div>
            <div class="card-body-pad">
                <div class="row g-2">
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#newBookingModal">
                            <div class="qa-icon"><i class="bi bi-calendar-plus"></i></div>
                            <span class="qa-label">New Booking</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-box-arrow-in-right"></i></div>
                            <span class="qa-label">Check In</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-box-arrow-right"></i></div>
                            <span class="qa-label">Check Out</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-receipt"></i></div>
                            <span class="qa-label">Generate Invoice</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-person-plus"></i></div>
                            <span class="qa-label">Add Customer</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-stars"></i></div>
                            <span class="qa-label">Housekeeping</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-currency-rupee"></i></div>
                            <span class="qa-label">Record Payment</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
                            <span class="qa-label">View Report</span>
                        </div>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="quick-action-btn">
                            <div class="qa-icon"><i class="bi bi-tools"></i></div>
                            <span class="qa-label">Maintenance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== ROW: Quick Actions + Hotel Performance ====== -->
<div class="row g-3 mb-4">
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="sh-card h-100">
            <div class="card-head">
                <h6><i class="bi bi-door-open"></i> Room Availability</h6>
                <span style="font-size:.72rem;font-weight:600;color:#10b981">Today</span>
            </div>
            <div class="card-body-pad">
                <!-- Overall -->
                <div class="text-center mb-3 p-3 rounded-3" style="background:var(--sh-bg)">
                    <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted)">Overall Occupancy</div>
                    <div style="font-size:2.25rem;font-weight:800;color:var(--sh-primary);line-height:1.1;margin:.25rem 0">82.6%</div>
                    <div class="progress" style="height:8px">
                        <div class="progress-bar bg-primary" style="width:82.6%"></div>
                    </div>
                    <div style="font-size:.72rem;color:var(--sh-text-muted);margin-top:.375rem">124 occupied / 150 total rooms</div>
                </div>
                <!-- Room Types -->
                <div class="room-type-row">
                    <div class="room-type-icon" style="background:rgba(59,130,246,.1);color:#3b82f6"><i class="bi bi-star"></i></div>
                    <div class="room-type-info">
                        <div class="room-type-name">Deluxe Rooms</div>
                        <div class="room-type-count">38/45 occupied</div>
                    </div>
                    <div class="room-type-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:84%;background:#3b82f6"></div>
                        </div>
                    </div>
                    <div class="room-type-pct">84%</div>
                </div>
                <div class="room-type-row">
                    <div class="room-type-icon" style="background:rgba(139,92,246,.1);color:#8b5cf6"><i class="bi bi-gem"></i></div>
                    <div class="room-type-info">
                        <div class="room-type-name">Suite Rooms</div>
                        <div class="room-type-count">18/20 occupied</div>
                    </div>
                    <div class="room-type-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:90%;background:#8b5cf6"></div>
                        </div>
                    </div>
                    <div class="room-type-pct">90%</div>
                </div>
                <div class="room-type-row">
                    <div class="room-type-icon" style="background:rgba(16,185,129,.1);color:#10b981"><i class="bi bi-house"></i></div>
                    <div class="room-type-info">
                        <div class="room-type-name">Standard Rooms</div>
                        <div class="room-type-count">52/65 occupied</div>
                    </div>
                    <div class="room-type-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:80%;background:#10b981"></div>
                        </div>
                    </div>
                    <div class="room-type-pct">80%</div>
                </div>
                <div class="room-type-row">
                    <div class="room-type-icon" style="background:rgba(245,158,11,.1);color:#f59e0b"><i class="bi bi-grid"></i></div>
                    <div class="room-type-info">
                        <div class="room-type-name">Family Rooms</div>
                        <div class="room-type-count">16/20 occupied</div>
                    </div>
                    <div class="room-type-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:80%;background:#f59e0b"></div>
                        </div>
                    </div>
                    <div class="room-type-pct">80%</div>
                </div>
            </div>
        </div>
    </div> 
    <!-- Hotel Performance Summary -->
    <div class="col-lg-4">
        <div class="sh-card">
            <div class="card-head">
                <h6><i class="bi bi-trophy"></i> Hotel Performance</h6>
                <span style="font-size:.72rem;font-weight:600;color:var(--sh-text-muted)">This Month</span>
            </div>
            <div class="card-body-pad">
                <div class="metric-row">
                    <div class="metric-label">Average Occupancy</div>
                    <div class="metric-bar">
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width:82%"></div>
                        </div>
                    </div>
                    <div class="metric-val">82%</div>
                </div>
                <div class="metric-row">
                    <div class="metric-label">RevPAR</div>
                    <div class="metric-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:74%;background:#10b981"></div>
                        </div>
                    </div>
                    <div class="metric-val" style="color:#10b981">74%</div>
                </div>
                <div class="metric-row">
                    <div class="metric-label">Guest Satisfaction</div>
                    <div class="metric-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:94%;background:#8b5cf6"></div>
                        </div>
                    </div>
                    <div class="metric-val" style="color:#8b5cf6">94%</div>
                </div>
                <div class="metric-row">
                    <div class="metric-label">Staff Performance</div>
                    <div class="metric-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:88%;background:#f59e0b"></div>
                        </div>
                    </div>
                    <div class="metric-val" style="color:#f59e0b">88%</div>
                </div>
                <div class="metric-row">
                    <div class="metric-label">Restaurant Utilization</div>
                    <div class="metric-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:67%;background:#06b6d4"></div>
                        </div>
                    </div>
                    <div class="metric-val" style="color:#06b6d4">67%</div>
                </div>
                <div class="metric-row">
                    <div class="metric-label">Repeat Guests</div>
                    <div class="metric-bar">
                        <div class="progress">
                            <div class="progress-bar" style="width:42%;background:#ec4899"></div>
                        </div>
                    </div>
                    <div class="metric-val" style="color:#ec4899">42%</div>
                </div>
                <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center">
                    <span style="font-size:.75rem;color:var(--sh-text-muted)">Overall Score</span>
                    <span style="font-size:1.25rem;font-weight:800;color:var(--sh-primary)">A+ <small style="font-size:.7rem;font-weight:600;color:#10b981">Excellent</small></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Notifications -->
    <div class="col-lg-4">
        <div class="sh-card">
            <div class="card-head">
                <h6><i class="bi bi-bell"></i> Latest Notifications</h6>
                <button class="btn-sh btn-sh-ghost btn-sh-sm">View All</button>
            </div>
            <div class="card-body-pad">
                <div class="notif-item">
                    <div class="notif-icon" style="background:rgba(16,185,129,.1)"><i class="bi bi-calendar-check" style="color:#10b981"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">New Booking Confirmed</div>
                        <div class="notif-msg">Priya Nair — Deluxe Room 204, 3 nights</div>
                        <div class="notif-time"><i class="bi bi-clock" style="font-size:.65rem"></i> 2 min ago</div>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="notif-icon" style="background:rgba(245,158,11,.1)"><i class="bi bi-wrench" style="color:#f59e0b"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">Maintenance Request</div>
                        <div class="notif-msg">Room 302 — AC malfunction reported</div>
                        <div class="notif-time"><i class="bi bi-clock" style="font-size:.65rem"></i> 15 min ago</div>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="notif-icon" style="background:rgba(29,78,216,.1)"><i class="bi bi-box-arrow-in-right" style="color:var(--sh-primary)"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">Check-in Completed</div>
                        <div class="notif-msg">Mohammed Ali — Suite 501</div>
                        <div class="notif-time"><i class="bi bi-clock" style="font-size:.65rem"></i> 1 hr ago</div>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="notif-icon" style="background:rgba(139,92,246,.1)"><i class="bi bi-currency-rupee" style="color:#8b5cf6"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">Payment Received</div>
                        <div class="notif-msg">₹12,500 — Ananya Pillai, Room 118</div>
                        <div class="notif-time"><i class="bi bi-clock" style="font-size:.65rem"></i> 3 hr ago</div>
                    </div>
                </div>
                <div class="notif-item">
                    <div class="notif-icon" style="background:rgba(239,68,68,.1)"><i class="bi bi-x-circle" style="color:#ef4444"></i></div>
                    <div class="notif-body">
                        <div class="notif-title">Booking Cancelled</div>
                        <div class="notif-msg">Vikram Singh — Room 115, Full refund</div>
                        <div class="notif-time"><i class="bi bi-clock" style="font-size:.65rem"></i> 5 hr ago</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ====== RECENT BOOKINGS TABLE ====== -->
<div class="sh-card mb-4">
    <div class="card-head">
        <h6><i class="bi bi-calendar-check"></i> Recent Bookings</h6>
        <div class="card-actions flex-wrap gap-2">
            <div style="position:relative">
                <i class="bi bi-search" style="position:absolute;left:.625rem;top:50%;transform:translateY(-50%);color:var(--sh-text-muted);font-size:.8rem;pointer-events:none"></i>
                <input id="bookingSearch" type="search" class="form-control form-control-sm" placeholder="Search bookings…" style="padding-left:2rem;width:180px" />
            </div>
            <div class="dropdown">
                <button class="btn-sh btn-sh-ghost btn-sh-sm dropdown-toggle" data-bs-toggle="dropdown">All Status</button>
                <ul class="dropdown-menu dropdown-menu-end" style="font-size:.82rem">
                    <li><a class="dropdown-item" href="#">All</a></li>
                    <li><a class="dropdown-item" href="#">Confirmed</a></li>
                    <li><a class="dropdown-item" href="#">Pending</a></li>
                    <li><a class="dropdown-item" href="#">Checked In</a></li>
                    <li><a class="dropdown-item" href="#">Checked Out</a></li>
                    <li><a class="dropdown-item" href="#">Cancelled</a></li>
                </ul>
            </div>
            <button class="btn-sh btn-sh-primary btn-sh-sm" data-bs-toggle="modal" data-bs-target="#newBookingModal"><i class="bi bi-plus-lg"></i> Add Booking</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table sh-table" id="bookingsTable">
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Guest Name</th>
                    <th>Room</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Nights</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBookings as $booking)
                    @php
                        $bookingStatus = match ($booking->status) {
                            'confirmed' => ['label' => 'Confirmed', 'class' => 'confirmed', 'icon' => 'bi-check-circle-fill'],
                            'checked_in' => ['label' => 'Checked In', 'class' => 'checked-in', 'icon' => 'bi-door-open'],
                            'checked_out' => ['label' => 'Checked Out', 'class' => 'checked-out', 'icon' => 'bi-box-arrow-right'],
                            'cancelled' => ['label' => 'Cancelled', 'class' => 'cancelled', 'icon' => 'bi-x-circle'],
                            'no_show' => ['label' => 'No Show', 'class' => 'cancelled', 'icon' => 'bi-slash-circle'],
                            default => ['label' => 'Pending', 'class' => 'pending', 'icon' => 'bi-clock'],
                        };

                        $bookingGuest = $booking->primaryGuest ? $booking->primaryGuest->full_name : ($booking->guest?->full_name ?? 'Guest');
                        $bookingEmail = $booking->primaryGuest ? $booking->primaryGuest->email : ($booking->guest?->email ?? 'N/A');
                        $roomNumbers = $booking->rooms->pluck('room_number')->implode(', ');
                        $nights = max(1, $booking->check_in_date && $booking->check_out_date ? \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) : 1);
                    @endphp
                    <tr>
                        <td><span style="font-family:monospace;font-weight:600;color:var(--sh-primary)">#BK-{{ $booking->id }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="guest-avatar" style="background:#{{ substr(md5($bookingGuest), 0, 6) }}">{{ strtoupper(substr($bookingGuest, 0, 2)) }}</div>
                                <div>
                                    <div class="fw-600" style="font-weight:600">{{ $bookingGuest }}</div>
                                    <div style="font-size:.7rem;color:var(--sh-text-muted)">{{ $bookingEmail }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="fw-600" style="font-weight:600">{{ $roomNumbers ?: 'N/A' }}</span> <span style="font-size:.72rem;color:var(--sh-text-muted)">{{ $booking->rooms->first()?->roomType?->name ?? 'Room' }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d M Y') }}</td>
                        <td>{{ $nights }}</td>
                        <td><span style="font-weight:700">₹{{ number_format((float) $booking->total_amount, 2) }}</span></td>
                        <td><span class="badge-status {{ $bookingStatus['class'] }}"><i class="bi {{ $bookingStatus['icon'] }}"></i> {{ $bookingStatus['label'] }}</span></td>
                        <td>
                            <button class="btn-sh btn-sh-ghost btn-sh-sm" data-bs-toggle="modal" data-bs-target="#bookingModal" data-name="{{ $bookingGuest }}" data-room="{{ $roomNumbers ?: 'N/A' }}" data-checkin="{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d M Y') }}" data-checkout="{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d M Y') }}" data-status="{{ $booking->status }}" data-amount="₹{{ number_format((float) $booking->total_amount, 2) }}"><i class="bi bi-eye"></i></button>
                            <button class="btn-sh btn-sh-ghost btn-sh-sm"><i class="bi bi-pencil"></i></button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">No recent booking records found for this hotel.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-top flex-wrap gap-2">
        <span style="font-size:.78rem;color:var(--sh-text-muted)">Showing 6 of 28 bookings</span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
            </ul>
        </nav>
    </div>
</div>

<!-- ====== CUSTOMER LIST TABLE ====== -->
<div class="sh-card mb-4">
    <div class="card-head">
        <h6><i class="bi bi-people"></i> Customer List</h6>
        <div class="card-actions flex-wrap gap-2">
            <div style="position:relative">
                <i class="bi bi-search" style="position:absolute;left:.625rem;top:50%;transform:translateY(-50%);color:var(--sh-text-muted);font-size:.8rem;pointer-events:none"></i>
                <input id="customerSearch" type="search" class="form-control form-control-sm" placeholder="Search customers…" style="padding-left:2rem;width:180px" />
            </div>
            <button class="btn-sh btn-sh-primary btn-sh-sm"><i class="bi bi-person-plus"></i> Add Customer</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table sh-table" id="customersTable">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Total Visits</th>
                    <th>Total Spend</th>
                    <th>Last Visit</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    @php
                        $customerVisits = \App\Models\Booking::whereHas('bookingGuests', function ($query) use ($customer) {
                            $query->where('guest_id', $customer->id);
                        })->count();

                        $customerSpend = \App\Models\Booking::whereHas('bookingGuests', function ($query) use ($customer) {
                            $query->where('guest_id', $customer->id);
                        })->sum('total_amount');

                        $customerInitials = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $customer->full_name), 0, 2));
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="guest-avatar" style="background:#{{ substr(md5($customer->full_name), 0, 6) }}">{{ $customerInitials ?: 'G' }}</div>
                                <div>
                                    <div style="font-weight:600">{{ $customer->full_name }}</div>
                                    <div style="font-size:.7rem;color:var(--sh-text-muted)">{{ $customer->email ?: 'No email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $customer->mobile ?: 'N/A' }}</td>
                        <td>{{ $customer->address ?: 'N/A' }}</td>
                        <td><strong>{{ $customerVisits }}</strong></td>
                        <td><strong style="color:#10b981">₹{{ number_format((float) $customerSpend, 2) }}</strong></td>
                        <td>{{ $customer->created_at ? $customer->created_at->format('d M Y') : 'N/A' }}</td>
                        <td><span class="badge-status confirmed"><i class="bi bi-person-check"></i> Guest</span></td>
                        <td><button class="btn-sh btn-sh-ghost btn-sh-sm"><i class="bi bi-eye"></i></button> <button class="btn-sh btn-sh-ghost btn-sh-sm"><i class="bi bi-pencil"></i></button></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No guest records found for this hotel.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-top flex-wrap gap-2">
        <span style="font-size:.78rem;color:var(--sh-text-muted)">Showing 5 of 1,284 customers</span>
        <nav>
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item disabled"><a class="page-link" href="#">&laquo;</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">&raquo;</a></li>
            </ul>
        </nav>
    </div>
</div>



<!-- ===================== BOOKING DETAIL MODAL ===================== -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingModalLabel"><i class="bi bi-calendar-check me-2 text-primary"></i>Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--sh-bg)">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Guest Name</div>
                            <div style="font-weight:700;font-size:.95rem" id="modal-guest-name">—</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--sh-bg)">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Room</div>
                            <div style="font-weight:700;font-size:.95rem" id="modal-room">—</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--sh-bg)">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Check-in</div>
                            <div style="font-weight:700;font-size:.95rem" id="modal-checkin">—</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--sh-bg)">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Check-out</div>
                            <div style="font-weight:700;font-size:.95rem" id="modal-checkout">—</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--sh-bg)">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.25rem">Amount</div>
                            <div style="font-weight:800;font-size:1.1rem;color:var(--sh-primary)" id="modal-amount">—</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded-3" style="background:var(--sh-bg)">
                            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sh-text-muted);margin-bottom:.375rem">Status</div>
                            <span class="badge-status confirmed" id="modal-status">—</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sh btn-sh-ghost" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn-sh btn-sh-outline"><i class="bi bi-printer"></i> Print Invoice</button>
                <button type="button" class="btn-sh btn-sh-primary"><i class="bi bi-pencil"></i> Edit Booking</button>
            </div>
        </div>
    </div>
</div>

<!-- ===================== NEW BOOKING MODAL ===================== -->
<div class="modal fade" id="newBookingModal" tabindex="-1" aria-labelledby="newBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newBookingModalLabel"><i class="bi bi-calendar-plus me-2 text-primary"></i>Create New Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="newBookingForm">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <label class="form-label">Guest Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter guest name" required />
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" placeholder="guest@email.com" required />
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" placeholder="+91 98765 43210" />
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Room Type <span class="text-danger">*</span></label>
                            <select class="form-select" required>
                                <option value="">Select room type…</option>
                                <option>Standard Room</option>
                                <option>Deluxe Room</option>
                                <option>Suite</option>
                                <option>Family Room</option>
                            </select>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Check-in Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" required />
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Check-out Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" required />
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Number of Guests</label>
                            <input type="number" class="form-control" placeholder="1" min="1" max="10" />
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label">Meal Plan</label>
                            <select class="form-select">
                                <option>Room Only</option>
                                <option>Bed &amp; Breakfast</option>
                                <option>Half Board</option>
                                <option>Full Board</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Special Requests</label>
                            <textarea class="form-control" rows="2" placeholder="Any special requests or notes…"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sh btn-sh-ghost" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="newBookingForm" class="btn-sh btn-sh-primary"><i class="bi bi-check-lg"></i> Create Booking</button>
            </div>
        </div>
    </div>
</div>


@endsection