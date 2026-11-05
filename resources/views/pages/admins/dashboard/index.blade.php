@extends('layouts.master')

@section('title', 'Super Admin Dashboard')
@section('mainbreadcrumb', '')

@section('content') 
@if(auth()->user()->hasPermission('dashboard', 'view'))

    <!-- ══════════════════════════════════════
         12 KPI CARDS
    ════════════════════════════════════════ -->
    <div class="row g-3 ">
      <div class="col-6 mb-3 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-blue">
          <div class="kpi-icon" style="background:rgba(59,130,246,.12);color:#3b82f6"><i class="bi bi-buildings"></i></div>
          <div class="kpi-val" data-count="{{ $data['total_hotels'] }}">{{ $data['total_hotels'] }}</div>
          <div class="kpi-label">Total Hotels</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+8 this month</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-green">
          <div class="kpi-icon" style="background:rgba(16,185,129,.12);color:#10b981"><i class="bi bi-check-circle"></i></div>
          <div class="kpi-val" data-count="{{ $data['total_active_hotels'] }}">{{ $data['total_active_hotels'] }}</div>
          <div class="kpi-label">Active Hotels</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>90.1% active</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-purple">
          <div class="kpi-icon" style="background:rgba(139,92,246,.12);color:#8b5cf6"><i class="bi bi-people"></i></div>
          <div class="kpi-val" data-count="{{ $data['total_users'] }}">{{ $data['total_users'] }}</div>
          <div class="kpi-label">Total Users</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+48 today</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-cyan">
          <div class="kpi-icon" style="background:rgba(6,182,212,.12);color:#06b6d4"><i class="bi bi-person-badge"></i></div>
          <div class="kpi-val" data-count="{{ $data['total_employees'] }}">{{ $data['total_employees'] }}</div>
          <div class="kpi-label">Total Employees</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+12 this week</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-orange">
          <div class="kpi-icon" style="background:rgba(249,115,22,.12);color:#f97316"><i class="bi bi-currency-rupee"></i></div>
          <div class="kpi-val">₹18.4L</div>
          <div class="kpi-label">Monthly Revenue</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+22% vs last month</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-indigo">
          <div class="kpi-icon" style="background:rgba(99,102,241,.12);color:#6366f1"><i class="bi bi-calendar-check"></i></div>
          <div class="kpi-val" data-count="{{ $data['total_bookings'] }}">{{ $data['total_bookings'] }}</div>
          <div class="kpi-label">Total Bookings</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+340 today</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-teal">
          <div class="kpi-icon" style="background:rgba(20,184,166,.12);color:#14b8a6"><i class="bi bi-grid-1x2"></i></div>
          <div class="kpi-val" data-count="{{ $data['active_subscriptions'] }}">{{ $data['active_subscriptions'] }}</div>
          <div class="kpi-label">Active Subscriptions</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+6 this month</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-red">
          <div class="kpi-icon" style="background:rgba(239,68,68,.12);color:#ef4444"><i class="bi bi-ticket-perforated"></i></div>
          <div class="kpi-val" data-count="{{ $data['support_tickets'] }}">{{ $data['support_tickets'] }}</div>
          <div class="kpi-label">Support Tickets</div>
          <span class="kpi-tag down"><i class="bi bi-exclamation-circle"></i>3 critical</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-sky">
          <div class="kpi-icon" style="background:rgba(14,165,233,.12);color:#0ea5e9"><i class="bi bi-door-open"></i></div>
          <div class="kpi-val" data-count="{{ $data['total_rooms'] }}">{{ $data['total_rooms'] }}</div>
          <div class="kpi-label">Total Rooms</div>
          <span class="kpi-tag neu"><i class="bi bi-dash"></i>Across 142 hotels</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-lime">
          <div class="kpi-icon" style="background:rgba(132,204,22,.12);color:#65a30d"><i class="bi bi-bar-chart-fill"></i></div>
          <div class="kpi-val">78.4%</div>
          <div class="kpi-label">Avg Occupancy Rate</div>
          <span class="kpi-tag up"><i class="bi bi-arrow-up-short"></i>+3.2% this month</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-amber">
          <div class="kpi-icon" style="background:rgba(245,158,11,.12);color:#f59e0b"><i class="bi bi-clock-history"></i></div>
          <div class="kpi-val" data-count="22">22</div>
          <div class="kpi-label">Trial Users</div>
          <span class="kpi-tag neu"><i class="bi bi-dash"></i>14-day trials</span>
        </div>
      </div>
      <div class="col-6 col-md-4 col-xl-2 mb-3">
        <div class="kpi-card c-pink">
          <div class="kpi-icon" style="background:rgba(236,72,153,.12);color:#ec4899"><i class="bi bi-calendar-x"></i></div>
          <div class="kpi-val" data-count="{{ $data['expired_subscriptions'] }}">{{ $data['expired_subscriptions'] }}</div>
          <div class="kpi-label">Expired Subscriptions</div>
          <span class="kpi-tag down"><i class="bi bi-arrow-up-short"></i>+4 vs last month</span>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         REVENUE CHART + HOTEL GROWTH CHART
    ════════════════════════════════════════ -->
    <div class="row g-3 mb-4">

      <!-- Revenue Chart -->
      <div class="col-lg-8">
        <div class="sa-card">
          <div class="c-head">
            <h6><i class="bi bi-bar-chart-line"></i> Monthly Revenue Analytics</h6>
            <div class="c-actions">
              <div class="dropdown">
                <button class="btn-sa btn-sa-ghost btn-sa-sm dropdown-toggle" data-bs-toggle="dropdown">This Year</button>
                <ul class="dropdown-menu dropdown-menu-end" style="font-size:.8rem">
                  <li><a class="dropdown-item" href="#">Last 6 Months</a></li>
                  <li><a class="dropdown-item" href="#">This Year</a></li>
                  <li><a class="dropdown-item" href="#">Last Year</a></li>
                </ul>
              </div>
              <button class="btn-sa btn-sa-ghost btn-sa-sm" data-refresh><i class="bi bi-arrow-clockwise"></i></button>
            </div>
          </div>
          <div class="c-pad">
            <div class="row g-3 mb-3">
              <div class="col-sm-3">
                <div class="p-3 rounded-3" style="background:var(--sa-bg)">
                  <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sa-text-muted);margin-bottom:.2rem">Total Revenue</div>
                  <div style="font-size:1.15rem;font-weight:800;color:var(--sa-text)">₹2.18Cr</div>
                  <div style="font-size:.7rem;color:#10b981;font-weight:600"><i class="bi bi-arrow-up-short"></i>+28% YoY</div>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="p-3 rounded-3" style="background:var(--sa-bg)">
                  <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sa-text-muted);margin-bottom:.2rem">Subscription</div>
                  <div style="font-size:1.15rem;font-weight:800;color:var(--sa-text)">₹1.46Cr</div>
                  <div style="font-size:.7rem;color:#10b981;font-weight:600"><i class="bi bi-arrow-up-short"></i>+34% YoY</div>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="p-3 rounded-3" style="background:var(--sa-bg)">
                  <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sa-text-muted);margin-bottom:.2rem">Add-on Services</div>
                  <div style="font-size:1.15rem;font-weight:800;color:var(--sa-text)">₹47.2L</div>
                  <div style="font-size:.7rem;color:#10b981;font-weight:600"><i class="bi bi-arrow-up-short"></i>+19% YoY</div>
                </div>
              </div>
              <div class="col-sm-3">
                <div class="p-3 rounded-3" style="background:var(--sa-bg)">
                  <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sa-text-muted);margin-bottom:.2rem">Pending</div>
                  <div style="font-size:1.15rem;font-weight:800;color:var(--sa-danger)">₹4.8L</div>
                  <div style="font-size:.7rem;color:#ef4444;font-weight:600"><i class="bi bi-exclamation-circle"></i>16 invoices</div>
                </div>
              </div>
            </div>
            <!-- Bar Chart -->
            <div class="chart-area" style="height:120px">
              <div class="chart-bar" style="height:52%" data-label="Jan ₹14.2L"></div>
              <div class="chart-bar" style="height:61%" data-label="Feb ₹16.8L"></div>
              <div class="chart-bar" style="height:55%" data-label="Mar ₹15.1L"></div>
              <div class="chart-bar" style="height:73%" data-label="Apr ₹20.1L"></div>
              <div class="chart-bar" style="height:66%" data-label="May ₹18.2L"></div>
              <div class="chart-bar" style="height:80%" data-label="Jun ₹22.0L"></div>
              <div class="chart-bar" style="height:75%" data-label="Jul ₹20.7L"></div>
              <div class="chart-bar active" style="height:92%" data-label="Aug ₹25.3L"></div>
              <div class="chart-bar" style="height:84%" data-label="Sep ₹23.1L"></div>
              <div class="chart-bar" style="height:78%" data-label="Oct ₹21.5L"></div>
              <div class="chart-bar" style="height:68%" data-label="Nov ₹18.7L"></div>
              <div class="chart-bar" style="height:88%" data-label="Dec ₹24.2L"></div>
            </div>
            <div class="chart-labels">
              <span>Jan</span><span>Feb</span><span>Mar</span><span>Apr</span>
              <span>May</span><span>Jun</span><span>Jul</span><span>Aug</span>
              <span>Sep</span><span>Oct</span><span>Nov</span><span>Dec</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Hotel Growth Chart -->
      <div class="col-lg-4">
        <div class="sa-card h-100">
          <div class="c-head">
            <h6><i class="bi bi-buildings"></i> Hotel Growth</h6>
            <span style="font-size:.72rem;font-weight:600;color:#10b981">+8 this month</span>
          </div>
          <div class="c-pad">
            <div class="text-center mb-3 p-3 rounded-3" style="background:var(--sa-bg)">
              <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--sa-text-muted)">Total Registered Hotels</div>
              <div style="font-size:2rem;font-weight:900;color:var(--sa-primary);line-height:1.1;margin:.25rem 0">{{ $data['total_hotels'] }}</div>
              <div class="progress" style="height:8px;margin-bottom:.375rem"><div class="progress-bar bg-primary" style="width:90%"></div></div>
              <div style="font-size:.72rem;color:var(--sa-text-muted)">{{ $data['active_subscriptions'] }} Active · {{ $data['expired_subscriptions'] }} Inactive</div>
            </div>
            <!-- Hotel type breakdown -->
            <div class="stat-row">
              <div class="sr-label">Budget Hotels</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:60%;background:#3b82f6"></div></div></div>
              <div class="sr-val">86</div>
            </div>
            <div class="stat-row">
              <div class="sr-label">3-Star</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:28%;background:#10b981"></div></div></div>
              <div class="sr-val">40</div>
            </div>
            <div class="stat-row">
              <div class="sr-label">4-Star</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:10%;background:#8b5cf6"></div></div></div>
              <div class="sr-val">12</div>
            </div>
            <div class="stat-row">
              <div class="sr-label">5-Star / Luxury</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:3%;background:#f59e0b"></div></div></div>
              <div class="sr-val">4</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         LATEST HOTELS TABLE + SUBSCRIPTION PLANS
    ════════════════════════════════════════ -->
    <div class="row g-3 mb-4">

      <!-- Registered Hotels Table -->
      <div class="col-lg-8">
        <div class="sa-card">
          <div class="c-head">
            <h6><i class="bi bi-buildings"></i> Latest Registered Hotels</h6>
            <div class="c-actions">
              <div style="position:relative">
                <i class="bi bi-search" style="position:absolute;left:.625rem;top:50%;transform:translateY(-50%);color:var(--sa-text-muted);font-size:.8rem;pointer-events:none"></i>
                <input id="hotelSearch" type="search" class="form-control form-control-sm" placeholder="Search hotels…" style="padding-left:2rem;width:170px" />
              </div>
              <div data-group>
                <button class="btn-sa btn-sa-primary btn-sa-sm active" data-filter="all" data-target="hotelsTable">All</button>
                <button class="btn-sa btn-sa-ghost btn-sa-sm" data-filter="active" data-target="hotelsTable">Active</button>
                <button class="btn-sa btn-sa-ghost btn-sa-sm" data-filter="trial" data-target="hotelsTable">Trial</button>
              </div>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table sa-table" id="hotelsTable">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Hotel Name</th>
                  <th>Owner</th>
                  <th>City</th>
                  <th>Plan</th>
                  <th>Rooms</th>
                  <th>Status</th>
                  <th>Registered</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($hotels as $hotel)
                @php
                    $avatar = strtoupper(substr($hotel->hotel_name, 0, 2));
                    $plan = $hotel->subscription && $hotel->subscription->plan ? $hotel->subscription->plan->name : 'N/A';
                    $planColors = [
                        'Pro' => ['color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,.1)'],
                        'Standard' => ['color' => '#10b981', 'bg' => 'rgba(16,185,129,.1)'],
                        'Enterprise' => ['color' => '#6366f1', 'bg' => 'rgba(99,102,241,.1)'],
                        'Trial' => ['color' => '#f59e0b', 'bg' => 'rgba(245,158,11,.1)'],
                        'default' => ['color' => '#6b7280', 'bg' => 'rgba(107,114,128,.1)']
                    ];
                    $planData = $planColors[$plan] ?? $planColors['default'];
                    $roomCount = \App\Models\RoomNumber::where('hotel_id', $hotel->id)->count();
                    $status = $hotel->status ? 'Active' : 'Inactive';
                    $statusClass = $hotel->status ? 'active' : 'inactive';
                    $statusIcon = $hotel->status ? 'bi-circle-fill' : 'bi-x-circle';
                @endphp
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">{{ $hotel->hotel_code }}</td>
                  <td><div class="d-flex align-items-center gap-2"><div class="m-av" style="background:{{ $planData['color'] }}">{{ $avatar }}</div><div><div style="font-weight:600">{{ $hotel->hotel_name }}</div><div style="font-size:.7rem;color:var(--sa-text-muted)">{{ $hotel->admin ? $hotel->admin->email : 'N/A' }}</div></div></div></td>
                  <td>{{ $hotel->admin ? $hotel->admin->name : 'N/A' }}</td>
                  <td>{{ $hotel->city ?: 'N/A' }}</td>
                  <td><span style="font-size:.72rem;font-weight:700;color:{{ $planData['color'] }};background:{{ $planData['bg'] }}; padding:.15rem .45rem;border-radius:999px">{{ $plan }}</span></td>
                  <td><strong>{{ $roomCount }}</strong></td>
                  <td><span class="b-status {{ $statusClass }}"><i class="bi {{ $statusIcon }}" style="font-size:.45rem"></i> {{ $status }}</span></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">{{ $hotel->created_at->format('d M Y') }}</td>
                  <td>
                    <button class="btn-sa btn-sa-ghost btn-sa-sm" data-bs-toggle="modal" data-bs-target="#hotelDetailModal" data-name="{{ $hotel->hotel_name }}" data-owner="{{ $hotel->admin ? $hotel->admin->name : 'N/A' }}" data-plan="{{ $plan }}" data-rooms="{{ $roomCount }}" data-status="{{ $status }}" data-city="{{ $hotel->city ?: 'N/A' }}"><i class="bi bi-eye"></i></button>
                    <button class="btn-sa btn-sa-ghost btn-sa-sm"><i class="bi bi-pencil"></i></button>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-top flex-wrap gap-2">
            <span style="font-size:.75rem;color:var(--sa-text-muted)">Showing 6 of 142 hotels</span>
            <nav><ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">3</a></li>
              <li class="page-item"><a class="page-link" href="#">»</a></li>
            </ul></nav>
          </div>
        </div>
      </div>

      <!-- Subscription Plans Overview -->
      <div class="col-lg-4">
        <div class="sa-card h-100">
          <div class="c-head">
            <h6><i class="bi bi-grid-1x2"></i> Subscription Plans</h6>
            <button class="btn-sa btn-sa-ghost btn-sa-sm">Manage</button>
          </div>
          <div class="c-pad">
            <div class="row g-2 mb-3">
              <div class="col-6 mb-3">
                <div class="plan-card">
                  <div class="plan-name">Starter</div>
                  <div class="plan-count">34</div>
                  <div style="font-size:.68rem;color:var(--sa-text-muted);margin:.25rem 0">hotels</div>
                  <div class="plan-price" style="font-size:1rem">₹999<span style="font-size:.7rem;font-weight:400;color:var(--sa-text-muted)">/mo</span></div>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="plan-card">
                  <div class="plan-name">Standard</div>
                  <div class="plan-count">52</div>
                  <div style="font-size:.68rem;color:var(--sa-text-muted);margin:.25rem 0">hotels</div>
                  <div class="plan-price" style="font-size:1rem">₹2,499<span style="font-size:.7rem;font-weight:400;color:var(--sa-text-muted)">/mo</span></div>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="plan-card popular">
                  <div class="plan-badge">Most Popular</div>
                  <div class="plan-name">Pro</div>
                  <div class="plan-count" style="color:#8b5cf6">38</div>
                  <div style="font-size:.68rem;color:var(--sa-text-muted);margin:.25rem 0">hotels</div>
                  <div class="plan-price" style="font-size:1rem">₹4,999<span style="font-size:.7rem;font-weight:400;color:var(--sa-text-muted)">/mo</span></div>
                </div>
              </div>
              <div class="col-6 mb-3">
                <div class="plan-card">
                  <div class="plan-name">Enterprise</div>
                  <div class="plan-count" style="color:#6366f1">4</div>
                  <div style="font-size:.68rem;color:var(--sa-text-muted);margin:.25rem 0">hotels</div>
                  <div class="plan-price" style="font-size:1rem">Custom</div>
                </div>
              </div>
            </div>
            <!-- Pending Renewals -->
            <div class="p-3 rounded-3 border" style="background:rgba(245,158,11,.04);border-color:rgba(245,158,11,.2)!important">
              <div style="font-size:.78rem;font-weight:700;color:var(--sa-text);margin-bottom:.625rem"><i class="bi bi-exclamation-triangle-fill me-1" style="color:#f59e0b"></i>Pending Renewals</div>
              <div class="stat-row" style="padding:.4rem 0">
                <div class="sr-label" style="font-size:.75rem">Expiring in 7 days</div>
                <div class="sr-val" style="color:#f59e0b">8</div>
              </div>
              <div class="stat-row" style="padding:.4rem 0">
                <div class="sr-label" style="font-size:.75rem">Expiring in 30 days</div>
                <div class="sr-val" style="color:#f59e0b">14</div>
              </div>
              <div class="stat-row" style="padding:.4rem 0;border-bottom:none">
                <div class="sr-label" style="font-size:.75rem">Already Expired</div>
                <div class="sr-val" style="color:#ef4444">14</div>
              </div>
              <button class="btn-sa btn-sa-outline w-100 mt-2 justify-content-center" style="font-size:.75rem"><i class="bi bi-send"></i> Send Renewal Reminders</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         PAYMENTS TABLE + SUPPORT TICKETS + TOP HOTELS
    ════════════════════════════════════════ -->
    <div class="row g-3 mb-4">

      <!-- Recent Payments -->
      <div class="col-lg-7">
        <div class="sa-card">
          <div class="c-head">
            <h6><i class="bi bi-credit-card"></i> Recent Payments</h6>
            <div class="c-actions">
              <div style="position:relative">
                <i class="bi bi-search" style="position:absolute;left:.625rem;top:50%;transform:translateY(-50%);color:var(--sa-text-muted);font-size:.8rem;pointer-events:none"></i>
                <input id="paySearch" type="search" class="form-control form-control-sm" placeholder="Search…" style="padding-left:2rem;width:150px" />
              </div>
              <button class="btn-sa btn-sa-ghost btn-sa-sm"><i class="bi bi-download"></i> Export</button>
            </div>
          </div>
          <div class="table-responsive">
            <table class="table sa-table" id="paymentsTable">
              <thead>
                <tr>
                  <th>Invoice</th>
                  <th>Hotel</th>
                  <th>Plan</th>
                  <th>Amount</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">#INV-4401</td>
                  <td>The Leela Palace</td>
                  <td>Pro Annual</td>
                  <td><strong>₹59,988</strong></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">16 Apr 2025</td>
                  <td><span class="b-status paid"><i class="bi bi-check-circle-fill"></i> Paid</span></td>
                </tr>
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">#INV-4402</td>
                  <td>Sunrise Residency</td>
                  <td>Standard Monthly</td>
                  <td><strong>₹2,499</strong></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">15 Apr 2025</td>
                  <td><span class="b-status paid"><i class="bi bi-check-circle-fill"></i> Paid</span></td>
                </tr>
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">#INV-4403</td>
                  <td>Raj Mahal Inn</td>
                  <td>Starter Trial</td>
                  <td><strong>₹0</strong></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">15 Apr 2025</td>
                  <td><span class="b-status trial"><i class="bi bi-clock"></i> Trial</span></td>
                </tr>
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">#INV-4404</td>
                  <td>Blue Gem Resort</td>
                  <td>Pro Monthly</td>
                  <td><strong>₹4,999</strong></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">14 Apr 2025</td>
                  <td><span class="b-status pending"><i class="bi bi-clock-history"></i> Pending</span></td>
                </tr>
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">#INV-4405</td>
                  <td>Hotel Orchid</td>
                  <td>Standard Annual</td>
                  <td><strong>₹24,999</strong></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">12 Apr 2025</td>
                  <td><span class="b-status failed"><i class="bi bi-x-circle"></i> Failed</span></td>
                </tr>
                <tr>
                  <td style="font-family:monospace;font-weight:600;color:var(--sa-primary)">#INV-4406</td>
                  <td>Grand Horizon</td>
                  <td>Enterprise Annual</td>
                  <td><strong>₹1,79,988</strong></td>
                  <td style="font-size:.78rem;color:var(--sa-text-muted)">10 Apr 2025</td>
                  <td><span class="b-status paid"><i class="bi bi-check-circle-fill"></i> Paid</span></td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="px-3 py-2 d-flex align-items-center justify-content-between border-top flex-wrap gap-2">
            <span style="font-size:.75rem;color:var(--sa-text-muted)">Showing 6 of 142 invoices</span>
            <nav><ul class="pagination pagination-sm mb-0">
              <li class="page-item disabled"><a class="page-link" href="#">«</a></li>
              <li class="page-item active"><a class="page-link" href="#">1</a></li>
              <li class="page-item"><a class="page-link" href="#">2</a></li>
              <li class="page-item"><a class="page-link" href="#">»</a></li>
            </ul></nav>
          </div>
        </div>
      </div>

      <!-- Support Tickets + Top Hotels -->
      <div class="col-lg-5 d-flex flex-column gap-3">

        <!-- Support Tickets -->
        <div class="sa-card mb-3">
          <div class="c-head">
            <h6><i class="bi bi-ticket-perforated"></i> Support Ticket Summary</h6>
            <button class="btn-sa btn-sa-primary btn-sa-sm"><i class="bi bi-arrow-right"></i> View All</button>
          </div>
          <div class="c-pad">
            <div class="row g-2 mb-3">
              <div class="col-4 text-center p-2 rounded-3" style="background:rgba(239,68,68,.06)">
                <div style="font-size:1.25rem;font-weight:800;color:#ef4444">5</div>
                <div style="font-size:.68rem;font-weight:600;color:var(--sa-text-muted)">Open</div>
              </div>
              <div class="col-4 text-center p-2 rounded-3" style="background:rgba(29,78,216,.06)">
                <div style="font-size:1.25rem;font-weight:800;color:var(--sa-primary)">6</div>
                <div style="font-size:.68rem;font-weight:600;color:var(--sa-text-muted)">In Progress</div>
              </div>
              <div class="col-4 text-center p-2 rounded-3" style="background:rgba(16,185,129,.06)">
                <div style="font-size:1.25rem;font-weight:800;color:#10b981">3</div>
                <div style="font-size:.68rem;font-weight:600;color:var(--sa-text-muted)">Resolved</div>
              </div>
            </div>
            <div class="notif-row" style="padding:.6rem 0">
              <div class="notif-ic" style="background:rgba(239,68,68,.1);width:30px;height:30px;font-size:.8rem"><i class="bi bi-exclamation-circle" style="color:#ef4444"></i></div>
              <div class="notif-body">
                <div class="notif-title" style="font-size:.78rem">Payment gateway failure — Hotel Orchid</div>
                <div class="notif-time">Critical · 45 min ago</div>
              </div>
              <span class="b-status open ms-auto flex-shrink-0">Open</span>
            </div>
            <div class="notif-row" style="padding:.6rem 0">
              <div class="notif-ic" style="background:rgba(29,78,216,.1);width:30px;height:30px;font-size:.8rem"><i class="bi bi-bug" style="color:var(--sa-primary)"></i></div>
              <div class="notif-body">
                <div class="notif-title" style="font-size:.78rem">Booking report export bug — Sunrise Residency</div>
                <div class="notif-time">Medium · 2 hr ago</div>
              </div>
              <span class="b-status in-progress ms-auto flex-shrink-0" style="white-space:nowrap">In Progress</span>
            </div>
            <div class="notif-row" style="padding:.6rem 0;border:none">
              <div class="notif-ic" style="background:rgba(16,185,129,.1);width:30px;height:30px;font-size:.8rem"><i class="bi bi-check2-circle" style="color:#10b981"></i></div>
              <div class="notif-body">
                <div class="notif-title" style="font-size:.78rem">Invoice PDF generation fixed — The Leela Palace</div>
                <div class="notif-time">Low · 5 hr ago</div>
              </div>
              <span class="b-status resolved ms-auto flex-shrink-0">Resolved</span>
            </div>
          </div>
        </div>

        <!-- Top Performing Hotels -->
        <div class="sa-card">
          <div class="c-head">
            <h6><i class="bi bi-trophy"></i> Top Performing Hotels</h6>
            <span style="font-size:.72rem;color:var(--sa-text-muted)">By Revenue</span>
          </div>
          <div class="c-pad">
            <div class="stat-row">
              <span style="font-size:.75rem;font-weight:700;color:var(--sa-text-muted);width:18px;flex-shrink:0">1.</span>
              <div class="m-av me-1" style="background:#6366f1;width:24px;height:24px;font-size:.6rem;flex-shrink:0">GH</div>
              <div class="sr-label" style="font-size:.78rem;font-weight:600">Grand Horizon</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:95%;background:#6366f1"></div></div></div>
              <div class="sr-val" style="color:#6366f1;width:56px">₹4.2L</div>
            </div>
            <div class="stat-row">
              <span style="font-size:.75rem;font-weight:700;color:var(--sa-text-muted);width:18px;flex-shrink:0">2.</span>
              <div class="m-av me-1" style="background:#3b82f6;width:24px;height:24px;font-size:.6rem;flex-shrink:0">LP</div>
              <div class="sr-label" style="font-size:.78rem;font-weight:600">The Leela Palace</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:80%;background:#3b82f6"></div></div></div>
              <div class="sr-val" style="color:#3b82f6;width:56px">₹3.6L</div>
            </div>
            <div class="stat-row">
              <span style="font-size:.75rem;font-weight:700;color:var(--sa-text-muted);width:18px;flex-shrink:0">3.</span>
              <div class="m-av me-1" style="background:#8b5cf6;width:24px;height:24px;font-size:.6rem;flex-shrink:0">BG</div>
              <div class="sr-label" style="font-size:.78rem;font-weight:600">Blue Gem Resort</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:65%;background:#8b5cf6"></div></div></div>
              <div class="sr-val" style="color:#8b5cf6;width:56px">₹2.9L</div>
            </div>
            <div class="stat-row" style="border:none">
              <span style="font-size:.75rem;font-weight:700;color:var(--sa-text-muted);width:18px;flex-shrink:0">4.</span>
              <div class="m-av me-1" style="background:#10b981;width:24px;height:24px;font-size:.6rem;flex-shrink:0">SR</div>
              <div class="sr-label" style="font-size:.78rem;font-weight:600">Sunrise Residency</div>
              <div class="sr-bar"><div class="progress"><div class="progress-bar" style="width:48%;background:#10b981"></div></div></div>
              <div class="sr-val" style="color:#10b981;width:56px">₹2.1L</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════════════════════════════════════
         ACTIVITY + QUICK ACTIONS + SYSTEM + NOTIFICATIONS
    ════════════════════════════════════════ -->
    <div class="row g-3 mb-4">

      <!-- Recent Activity Timeline -->
      <div class="col-lg-4">
        <div class="sa-card">
          <div class="c-head">
            <h6><i class="bi bi-activity"></i> Recent Activity</h6>
            <button class="btn-sa btn-sa-ghost btn-sa-sm">See All</button>
          </div>
          <div class="c-pad">
            <div class="timeline">
              <div class="tl-item">
                <div class="tl-dot" style="background:#10b981"></div>
                <div class="tl-time">2 min ago</div>
                <div class="tl-text">New hotel registered</div>
                <div class="tl-sub">The Leela Palace · Udaipur · Pro Plan</div>
              </div>
              <div class="tl-item">
                <div class="tl-dot" style="background:#3b82f6"></div>
                <div class="tl-time">15 min ago</div>
                <div class="tl-text">Payment received</div>
                <div class="tl-sub">Grand Horizon · ₹1,79,988 · Enterprise Annual</div>
              </div>
              <div class="tl-item">
                <div class="tl-dot" style="background:#f59e0b"></div>
                <div class="tl-time">45 min ago</div>
                <div class="tl-text">Support ticket opened</div>
                <div class="tl-sub">Hotel Orchid · Payment gateway issue</div>
              </div>
              <div class="tl-item">
                <div class="tl-dot" style="background:#8b5cf6"></div>
                <div class="tl-time">1 hr ago</div>
                <div class="tl-text">Subscription upgraded</div>
                <div class="tl-sub">Blue Gem Resort · Starter → Pro</div>
              </div>
              <div class="tl-item">
                <div class="tl-dot" style="background:#ef4444"></div>
                <div class="tl-time">2 hr ago</div>
                <div class="tl-text">Hotel suspended</div>
                <div class="tl-sub">Hotel Orchid · Payment failure</div>
              </div>
              <div class="tl-item">
                <div class="tl-dot" style="background:#06b6d4"></div>
                <div class="tl-time">3 hr ago</div>
                <div class="tl-text">48 new users registered</div>
                <div class="tl-sub">Across all active hotels today</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions + DAU Card -->
      <div class="col-lg-4 d-flex flex-column gap-3">
        <!-- Quick Actions -->
        <div class="sa-card mb-3">
          <div class="c-head"><h6><i class="bi bi-lightning-charge"></i> Quick Actions</h6></div>
          <div class="c-pad">
            <div class="row g-2">
              <div class="col-4 mb-3"><div class="qa-btn" data-bs-toggle="modal" data-bs-target="#addHotelModal"><div class="qa-ic"><i class="bi bi-buildings"></i></div><span class="qa-lbl">Add Hotel</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-person-plus"></i></div><span class="qa-lbl">Add Owner</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-grid-1x2"></i></div><span class="qa-lbl">New Plan</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-receipt"></i></div><span class="qa-lbl">Generate Invoice</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-send"></i></div><span class="qa-lbl">Send Renewal</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-bar-chart-line"></i></div><span class="qa-lbl">View Reports</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-bell"></i></div><span class="qa-lbl">Broadcast</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-shield-check"></i></div><span class="qa-lbl">Security Log</span></div></div>
              <div class="col-4 mb-3"><div class="qa-btn"><div class="qa-ic"><i class="bi bi-gear"></i></div><span class="qa-lbl">Settings</span></div></div>
            </div>
          </div>
        </div>

        <!-- Daily Active Users Card -->
        <div class="sa-card">
          <div class="c-head">
            <h6><i class="bi bi-person-check"></i> Daily Active Users</h6>
            <span style="font-size:.72rem;font-weight:600;color:#10b981">+12% vs yesterday</span>
          </div>
          <div class="c-pad">
            <div class="d-flex align-items-end gap-4 mb-3">
              <div>
                <div style="font-size:2rem;font-weight:900;color:var(--sa-text);line-height:1">1,284</div>
                <div style="font-size:.73rem;color:var(--sa-text-muted)">Active today</div>
              </div>
              <div class="flex-1">
                <div class="d-flex align-items-end gap-1" style="height:40px">
                  <div style="flex:1;height:40%;background:rgba(29,78,216,.2);border-radius:3px 3px 0 0"></div>
                  <div style="flex:1;height:60%;background:rgba(29,78,216,.2);border-radius:3px 3px 0 0"></div>
                  <div style="flex:1;height:55%;background:rgba(29,78,216,.2);border-radius:3px 3px 0 0"></div>
                  <div style="flex:1;height:75%;background:rgba(29,78,216,.2);border-radius:3px 3px 0 0"></div>
                  <div style="flex:1;height:65%;background:rgba(29,78,216,.2);border-radius:3px 3px 0 0"></div>
                  <div style="flex:1;height:90%;background:rgba(29,78,216,.2);border-radius:3px 3px 0 0"></div>
                  <div style="flex:1;height:100%;background:var(--sa-primary);border-radius:3px 3px 0 0"></div>
                </div>
              </div>
            </div>
            <div class="row g-2">
              <div class="col-4 text-center p-2 rounded-3" style="background:var(--sa-bg)">
                <div style="font-size:.95rem;font-weight:800;color:var(--sa-text)">842</div>
                <div style="font-size:.66rem;color:var(--sa-text-muted)">Hotel Staff</div>
              </div>
              <div class="col-4 text-center p-2 rounded-3" style="background:var(--sa-bg)">
                <div style="font-size:.95rem;font-weight:800;color:var(--sa-text)">312</div>
                <div style="font-size:.66rem;color:var(--sa-text-muted)">Managers</div>
              </div>
              <div class="col-4 text-center p-2 rounded-3" style="background:var(--sa-bg)">
                <div style="font-size:.95rem;font-weight:800;color:var(--sa-text)">130</div>
                <div style="font-size:.66rem;color:var(--sa-text-muted)">Admins</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- System Status + Notifications -->
      <div class="col-lg-4 d-flex flex-column gap-3">
        <!-- Server & System Status -->
        <div class="sa-card  mb-3">
          <div class="c-head">
            <h6><i class="bi bi-hdd-network"></i> Server &amp; System Status</h6>
            <span style="font-size:.7rem;font-weight:700;background:rgba(16,185,129,.12);color:#059669;padding:.2rem .55rem;border-radius:999px"><i class="bi bi-circle-fill me-1" style="font-size:.4rem"></i>All Operational</span>
          </div>
          <div class="c-pad">
            <div class="srv-row">
              <div class="srv-dot" style="background:#10b981"></div>
              <div class="srv-label">API Server</div>
              <div class="srv-val">99.98% uptime</div>
            </div>
            <div class="srv-row">
              <div class="srv-dot" style="background:#10b981"></div>
              <div class="srv-label">Database Cluster</div>
              <div class="srv-val">Normal</div>
            </div>
            <div class="srv-row">
              <div class="srv-dot" style="background:#10b981"></div>
              <div class="srv-label">Payment Gateway</div>
              <div class="srv-val">Operational</div>
            </div>
            <div class="srv-row">
              <div class="srv-dot" style="background:#f59e0b"></div>
              <div class="srv-label">Email Service</div>
              <div class="srv-val">Minor Delay</div>
            </div>
            <div class="srv-row">
              <div class="srv-dot" style="background:#10b981"></div>
              <div class="srv-label">CDN / Storage</div>
              <div class="srv-val">Fast</div>
            </div>
            <div class="srv-row" style="border:none">
              <div class="srv-dot" style="background:#10b981"></div>
              <div class="srv-label">Backup Service</div>
              <div class="srv-val">Last: 2 hr ago</div>
            </div>
            <div class="mt-3 p-2 rounded-3" style="background:var(--sa-bg)">
              <div class="d-flex justify-content-between mb-1"><span style="font-size:.75rem;font-weight:600">CPU Usage</span><span style="font-size:.75rem;font-weight:700;color:var(--sa-primary)">34%</span></div>
              <div class="progress mb-2"><div class="progress-bar bg-primary" style="width:34%"></div></div>
              <div class="d-flex justify-content-between mb-1"><span style="font-size:.75rem;font-weight:600">Memory Usage</span><span style="font-size:.75rem;font-weight:700;color:#8b5cf6">61%</span></div>
              <div class="progress"><div class="progress-bar" style="width:61%;background:#8b5cf6"></div></div>
            </div>
          </div>
        </div>

        <!-- System Notifications -->
        <div class="sa-card flex-1">
          <div class="c-head">
            <h6><i class="bi bi-bell"></i> System Notifications</h6>
            <button class="btn-sa btn-sa-ghost btn-sa-sm">View All</button>
          </div>
          <div class="c-pad">
            <div class="notif-row">
              <div class="notif-ic" style="background:rgba(245,158,11,.1)"><i class="bi bi-exclamation-triangle" style="color:#f59e0b"></i></div>
              <div class="notif-body">
                <div class="notif-title">8 subscriptions expiring soon</div>
                <div class="notif-msg">Within 7 days — send renewal reminders</div>
                <div class="notif-time"><i class="bi bi-clock" style="font-size:.6rem"></i> Today</div>
              </div>
            </div>
            <div class="notif-row">
              <div class="notif-ic" style="background:rgba(16,185,129,.1)"><i class="bi bi-buildings" style="color:#10b981"></i></div>
              <div class="notif-body">
                <div class="notif-title">New hotel onboarded</div>
                <div class="notif-msg">The Leela Palace is now live on Pro Plan</div>
                <div class="notif-time"><i class="bi bi-clock" style="font-size:.6rem"></i> 3 min ago</div>
              </div>
            </div>
            <div class="notif-row" style="border:none">
              <div class="notif-ic" style="background:rgba(99,102,241,.1)"><i class="bi bi-arrow-up-circle" style="color:#6366f1"></i></div>
              <div class="notif-body">
                <div class="notif-title">System update available</div>
                <div class="notif-msg">v3.4.1 — Booking module performance improvements</div>
                <div class="notif-time"><i class="bi bi-clock" style="font-size:.6rem"></i> 6 hr ago</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
@else
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i> You do not have permission to view the dashboard.
    </div>
@endif
@endsection