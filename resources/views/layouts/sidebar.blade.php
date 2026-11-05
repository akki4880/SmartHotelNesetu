<aside class="page-sidebar">
    <div class="page-logo">
        <a href="#"
            class="page-logo-link press-scale-down d-flex align-items-center position-relative justify-content-center"
            data-toggle="modal" data-target="#modal-shortcut">
            <img src="{{asset('assets/img/logo.png')}}" alt="SmartAdmin WebApp" aria-roledescription="logo">
            <span class="position-absolute text-white opacity-50 small pos-top pos-right mr-2 mt-n2"></span>
            <i class="fal fa-angle-down d-inline-block ml-1 fs-lg color-primary-300"></i>
        </a>
    </div>
    <!-- BEGIN PRIMARY NAVIGATION -->
    <nav id="js-primary-nav" class="primary-nav" role="navigation">
        <div class="nav-filter">
            <div class="position-relative">
                <input type="text" id="nav_filter_input" placeholder="Filter menu" class="form-control" tabindex="0">
                <a href="#" onclick="return false;" class="btn-primary btn-search-close js-waves-off"
                    data-action="toggle" data-class="list-filter-active" data-target=".page-sidebar">
                    <i class="fal fa-chevron-up"></i>
                </a>
            </div>
        </div>
        <div class="info-card">
            <!-- <img src="{{asset('assets/img/demo/avatars/avatar-admin.png')}}" class="profile-image rounded-circle" alt="{{ Auth::user()->name ?? ''}}"> -->
            <div class="profile-initials rounded-circle">
                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="info-card-text">
                <a href="#" class="d-flex align-items-center text-white">
                    <span class="text-truncate text-truncate-sm d-inline-block">
                        {{ Auth::user()->name ?? '' }}
                    </span>
                </a>
                <span class="d-inline-block text-truncate text-truncate-sm">{{ Auth::user()->email ?? '' }}</span>
            </div>
            <img src="{{asset('assets/img/card-backgrounds/cover-2-lg.png')}}" class="cover" alt="cover">
            <a href="#" onclick="return false;" class="pull-trigger-btn" data-action="toggle"
                data-class="list-filter-active" data-target=".page-sidebar" data-focus="nav_filter_input">
                <i class="fal fa-angle-down"></i>
            </a>
        </div>
        <ul id="js-nav-menu" class="nav-menu">

            @if(auth()->user()->hasPermission('dashboard', 'view'))
            <li class="{{ request()->is('dashboard') ? 'active' : '' }}">
                <a href="{{url('/dashboard')}}" title="Dashboard" data-filter-tags="dashboard">
                    <span class="nav-link-text" data-i18n="nav.dashboard"><i class="fas fa-gauge-high"></i>
                        &nbsp; Dashboard</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('user_role', 'view'))
            <li class="{{ request()->is('userroles') ? 'active' : '' }}">
                <a href="{{url('/userroles')}}" title="User Role" data-filter-tags="user role">
                    <span class="nav-link-text" data-i18n="nav.user_role"><i class="fas fa-user-tag"></i>
                        &nbsp; User Role</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('actions', 'view'))
            <li class="{{ request()->is('actions') ? 'active' : '' }}">
                <a href="{{url('/actions')}}" title="Actions" data-filter-tags="actions">
                    <span class="nav-link-text" data-i18n="nav.actions"><i class="fas fa-bolt"></i>
                        &nbsp; Actions</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('permissions', 'view'))
            <li class="{{ request()->is('permissions') ? 'active' : '' }}">
                <a href="{{ url('/permissions') }}" title="Permissions" data-filter-tags="permissions">
                    <span class="nav-link-text" data-i18n="nav.permissions">
                        <i class="fas fa-key"></i>
                        &nbsp; Permissions
                    </span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('admins', 'view'))
            <li class="{{ request()->is('admins') ? 'active' : '' }}">
                <a href="{{ url('/admins') }}" title="Admins" data-filter-tags="admins">
                    <span class="nav-link-text" data-i18n="nav.admins">
                        <i class="fas fa-user-shield"></i>
                        &nbsp; Admins
                    </span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('plans', 'view'))
            <li class="{{ request()->is('plans') ? 'active' : '' }}">
                <a href="{{ url('/plans') }}" title="Plans" data-filter-tags="plans">
                    <span class="nav-link-text" data-i18n="nav.plans"><i class="fas fa-clipboard-list"></i>
                        &nbsp; Plans
                    </span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('hotels', 'view'))
            <li class="{{ request()->is('hotels') ? 'active' : '' }}">
                <a href="{{url('/hotels')}}" title="Hotels" data-filter-tags="hotels">
                    <span class="nav-link-text" data-i18n="nav.hotels"><i class="fas fa-building"></i>
                        &nbsp; Hotels</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('developers', 'view'))
            <li class="{{ request()->is('developers*') ? 'active' : '' }}">
                <a href="{{ url('/developers') }}" title="Developers" data-filter-tags="developers">
                    <span class="nav-link-text" data-i18n="nav.developers">
                        <i class="fas fa-code"></i>
                        &nbsp; Developers
                    </span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('contact_requests', 'view'))
            <li class="{{ request()->is('contact-requests') ? 'active' : '' }}">
                <a href="{{url('/contact-requests')}}" title="Contact Requests" data-filter-tags="contact requests">
                    <span class="nav-link-text" data-i18n="nav.contact_requests"><i class="fas fa-envelope"></i>
                        &nbsp; Contact Requests</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('bookings', 'view'))
            <li class="{{ request()->is('bookings') ? 'active' : '' }}">
                <a href="{{url('/bookings')}}" title="Bookings" data-filter-tags="bookings">
                    <span class="nav-link-text" data-i18n="nav.bookings"><i class="fas fa-calendar-check"></i>
                        &nbsp; Bookings</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('book_map', 'view'))
            <li class="{{ request()->is('bookmap*') ? 'active' : '' }}">
                <a href="{{ url('/bookmap') }}" title="bookmap">
                    <span class="nav-link-text"><i class="fas fa-history"></i>&nbsp; Book Map</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('generate_invoice', 'view') || auth()->user()->hasPermission('all_invoices', 'view'))
            <li class="{{ request()->is('invoice-management*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" title="Invoice Management" data-filter-tags="invoice management">
                    <span class="nav-link-text">
                        <i class="fas fa-file-invoice"></i> &nbsp; Invoice Management
                    </span>
                </a>

                <ul>
                    {{-- Generate Invoice --}}
                    @if(auth()->user()->hasPermission('generate_invoice', 'view'))
                    <li class="{{ request()->is('invoice-management/generate-invoice*') ? 'active' : '' }}">
                        <a href="{{ url('invoice-management/generate-invoice') }}" title="Generate Invoice">
                            <span class="nav-link-text">
                                <i class="fas fa-receipt"></i> &nbsp; Generate Invoice
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('all_invoices', 'view'))
                    {{-- All Invoices --}}
                    <li class="{{ request()->is('invoice-management/invoices*') ? 'active' : '' }}">
                        <a href="{{ url('invoice-management/invoices') }}" title="All Invoices">
                            <span class="nav-link-text">
                                <i class="fas fa-file-alt"></i> &nbsp; All Invoices
                            </span>
                        </a>
                    </li>
                    @endif


                </ul>
            </li>
            @endif

            @if(auth()->user()->hasPermission('price_chart', 'view'))
            <li class="{{ request()->is('pricechart') ? 'active' : '' }}">
                <a href="{{url('/pricechart')}}" title="Price Chart" data-filter-tags="pricechart">
                    <span class="nav-link-text" data-i18n="nav.pricechart"><i class="fas fa-chart-line"></i>
                        &nbsp; Price Chart</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('guests', 'view'))
            <li class="{{ request()->is('guests') ? 'active' : '' }}">
                <a href="{{url('/guests')}}" title="Guests" data-filter-tags="guests">
                    <span class="nav-link-text" data-i18n="nav.guests"><i class="fas fa-user-friends"></i>
                        &nbsp; Guests</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('room_types', 'view') || auth()->user()->hasPermission('room_numbers', 'view') || auth()->user()->hasPermission('room_rate_setup', 'view') || auth()->user()->hasPermission('seasonal_room_rate_setup', 'view') || auth()->user()->hasPermission('hourly_room_rate_setup', 'view'))
            <li class="{{ request()->is('room-management*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" title="Room Management" data-filter-tags="room management">
                    <span class="nav-link-text" data-i18n="nav.room_management">
                        <i class="fas fa-bed"></i> &nbsp; Room Management
                    </span>
                </a>

                <ul>
                    {{-- Room Types --}}
                    @if(auth()->user()->hasPermission('room_types', 'view'))
                    <li class="{{ request()->is('room-management/room-types*') ? 'active' : '' }}">
                        <a href="{{ url('room-management/room-types') }}" title="Room Types"
                            data-filter-tags="room types">
                            <span class="nav-link-text">
                                <i class="fas fa-layer-group"></i>&nbsp; Room Types
                            </span>
                        </a>
                    </li>
                    @endif

                    {{-- Room Numbers --}}
                    @if(auth()->user()->hasPermission('room_numbers', 'view'))
                    <li class="{{ request()->is('room-management/room-numbers*') ? 'active' : '' }}">
                        <a href="{{ url('room-management/room-numbers') }}" title="Room Numbers"
                            data-filter-tags="room numbers">
                            <span class="nav-link-text">
                                <i class="fas fa-door-closed"></i> &nbsp; Room Numbers
                            </span>
                        </a>
                    </li>
                    @endif

                    @php
                    $roomRateTypes = auth()->user()->hotel->room_rate_type ?? [];
                    @endphp
                    @if(auth()->user()->hasPermission('room_rate_setup', 'view'))
                    @if(in_array('room', $roomRateTypes))
                    <li class="{{ request()->is('room-management/room-rates*') ? 'active' : '' }}">
                        <a href="{{ url('room-management/room-rates') }}" title="Room Rate Setup"
                            data-filter-tags="room rate setup">
                            <span class="nav-link-text">
                                <i class="fas fa-indian-rupee-sign"></i> &nbsp; Room Rate Setup
                            </span>
                        </a>
                    </li>
                    @endif
                    @endif

                    @if(auth()->user()->hasPermission('seasonal_room_rate_setup', 'view'))
                    <li class="{{ request()->is('room-management/seasonal-rates*') ? 'active' : '' }}">
                        <a href="{{ url('room-management/seasonal-rates') }}" title="Seasonal Room Rate Setup"
                            data-filter-tags="seasonal room rate setup">
                            <span class="nav-link-text">
                                <i class="fas fa-calendar-alt"></i> &nbsp; Seasonal Room Rate Setup
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('hourly_room_rate_setup', 'view'))
                    @if(in_array('hourly', $roomRateTypes))
                    <li class="{{ request()->is('room-management/hourly-rates*') ? 'active' : '' }}">
                        <a href="{{ url('room-management/hourly-rates') }}" title="Hourly Room Rate Setup"
                            data-filter-tags="hourly room rate setup">
                            <span class="nav-link-text">
                                <i class="fas fa-clock"></i> &nbsp; Hourly Room Rate Setup
                            </span>
                        </a>
                    </li>
                    @endif
                    @endif
                </ul>
            </li>
            @endif

            @if(auth()->user()->hasPermission('house_keeping', 'view'))
            <li class="{{ request()->is('housekeeping') ? 'active' : '' }}">
                <a href="{{url('/housekeeping')}}" title="House Keeping" data-filter-tags="housekeeping">
                    <span class="nav-link-text" data-i18n="nav.housekeeping"><i class="fas fa-calendar-check"></i>
                        &nbsp; House Keeping</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('agents', 'view'))
            <li class="{{ request()->is('agents') ? 'active' : '' }}">
                <a href="{{ url('/agents') }}" title="Agents" data-filter-tags="agents">
                    <span class="nav-link-text" data-i18n="nav.agents">
                        <i class="fas fa-user-tie"></i>
                        &nbsp; Agents
                    </span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('category_master', 'view') || auth()->user()->hasPermission('unit_master', 'view') || auth()->user()->hasPermission('vendor_master', 'view') || auth()->user()->hasPermission('item_master', 'view') || auth()->user()->hasPermission('purchase_order', 'view') || auth()->user()->hasPermission('goods_receipt', 'view') || auth()->user()->hasPermission('stock_ledger', 'view') || auth()->user()->hasPermission('consumption', 'view') || auth()->user()->hasPermission('stock_adjustments', 'view') || auth()->user()->hasPermission('stock_transactions', 'view'))
            <li class="{{ request()->is('inventory-management*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" title="Inventory Management" data-filter-tags="inventory management">
                    <span class="nav-link-text">
                        <i class="fas fa-boxes"></i> &nbsp; Inventory Management
                    </span>
                </a>

                <ul>

                    @if(auth()->user()->hasPermission('category_master', 'view'))
                    {{-- Category --}}
                    <li class="{{ request()->is('inventory-management/categories*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/categories') }}" title="Category Master">
                            <span class="nav-link-text">
                                <i class="fas fa-layer-group"></i> &nbsp; Category Master
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('unit_master', 'view'))
                    {{-- Unit --}}
                    <li class="{{ request()->is('inventory-management/units*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/units') }}" title="Unit Master">
                            <span class="nav-link-text">
                                <i class="fas fa-ruler"></i> &nbsp; Unit Master
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('vendor_master', 'view'))
                    {{-- Vendor --}}
                    <li class="{{ request()->is('inventory-management/vendors*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/vendors') }}" title="Vendor Master">
                            <span class="nav-link-text">
                                <i class="fas fa-truck"></i> &nbsp; Vendor Master
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('item_master', 'view'))
                    {{-- Item --}}
                    <li class="{{ request()->is('inventory-management/items*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/items') }}" title="Item Master">
                            <span class="nav-link-text">
                                <i class="fas fa-box"></i> &nbsp; Item Master
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('purchase_orders', 'view'))
                    {{-- Purchase Orders --}}
                    <li class="{{ request()->is('inventory-management/purchase-orders*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/purchase-orders') }}" title="Purchase Orders">
                            <span class="nav-link-text">
                                <i class="fas fa-file-invoice-dollar"></i> &nbsp; Purchase Orders
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('goods_receipt', 'view'))
                    {{-- GRN --}}
                    <li class="{{ request()->is('inventory-management/grn*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/grn') }}" title="Goods Receipt">
                            <span class="nav-link-text">
                                <i class="fas fa-truck-loading"></i> &nbsp; Goods Receipt (GRN)
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('stock_ledger', 'view'))
                    {{-- Stock Ledger --}}
                    <li class="{{ request()->is('inventory-management/stock-ledger') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/stock-ledger') }}" title="Stock Ledger">
                            <span class="nav-link-text">
                                <i class="fas fa-book"></i> &nbsp; Stock Ledger
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('consumption', 'view'))
                    {{-- Consumption --}}
                    <li class="{{ request()->is('inventory-management/consumption*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/consumption') }}" title="Consumption">
                            <span class="nav-link-text">
                                <i class="fas fa-utensils"></i> &nbsp; Consumption
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('stock_adjustments', 'view'))
                    {{-- Adjustments --}}
                    <li class="{{ request()->is('inventory-management/stock-adjustments*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/stock-adjustments') }}" title="Stock Adjustment">
                            <span class="nav-link-text">
                                <i class="fas fa-sliders-h"></i> &nbsp; Stock Adjustments
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('stock_transactions', 'view'))
                    {{-- Stock Overview --}}
                    <li class="{{ request()->is('inventory-management/stock-transactions*') ? 'active' : '' }}">
                        <a href="{{ url('inventory-management/stock-transactions') }}" title="Stock Overview">
                            <span class="nav-link-text">
                                <i class="fas fa-chart-bar"></i> &nbsp; Stock
                                Transactions
                            </span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            @if(auth()->user()->hasPermission('departments', 'view') || auth()->user()->hasPermission('roles', 'view') || auth()->user()->hasPermission('shifts', 'view') || auth()->user()->hasPermission('holidays', 'view') || auth()->user()->hasPermission('employees', 'view') || auth()->user()->hasPermission('attendance', 'view') || auth()->user()->hasPermission('leaves', 'view') || auth()->user()->hasPermission('payroll', 'view') || auth()->user()->hasPermission('reports', 'view'))
            <li class="{{ request()->is('employee-management*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" title="Employee Management" data-filter-tags="Employee Management">
                    <span class="nav-link-text">
                        <i class="fas fa-users"></i> &nbsp; Employee Management
                    </span>
                </a>

                <ul>
                    @if(auth()->user()->hasPermission('departments', 'view'))
                    {{-- Departments --}}
                    <li class="{{ request()->is('employee-management/departments*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/departments') }}" title="Departments">
                            <span class="nav-link-text">
                                <i class="fas fa-building"></i> &nbsp; Departments
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('roles', 'view'))
                    {{-- Roles --}}
                    <li class="{{ request()->is('employee-management/roles*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/roles') }}" title="Roles">
                            <span class="nav-link-text">
                                <i class="fas fa-user-shield"></i> &nbsp; Roles
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('shifts', 'view'))
                    {{-- Shifts --}}
                    <li class="{{ request()->is('employee-management/shifts*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/shifts') }}" title="Shifts">
                            <span class="nav-link-text">
                                <i class="fas fa-clock"></i> &nbsp; Shifts
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('holidays', 'view'))
                    {{-- Holidays --}}
                    <li class="{{ request()->is('employee-management/holidays*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/holidays') }}" title="Holidays">
                            <span class="nav-link-text">
                                <i class="fas fa-calendar"></i> &nbsp; Holidays
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('employees', 'view'))
                    {{-- Employees --}}
                    <li class="{{ request()->is('employee-management/employees*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/employees') }}" title="Employees">
                            <span class="nav-link-text">
                                <i class="fas fa-user-tie"></i> &nbsp; Employees
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('attendance', 'view'))
                    {{-- Attendance --}}
                    <li class="{{ request()->is('employee-management/attendances*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/attendances') }}" title="Attendance">
                            <span class="nav-link-text">
                                <i class="fas fa-calendar-check"></i> &nbsp; Attendance
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('leaves', 'view'))
                    {{-- Leaves --}}
                    <li class="{{ request()->is('employee-management/leaves*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/leaves') }}" title="Leaves">
                            <span class="nav-link-text">
                                <i class="fas fa-calendar-times"></i> &nbsp; Leaves
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('payroll', 'view'))
                    {{-- Payroll --}}
                    <li class="{{ request()->is('employee-management/payroll*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/payroll') }}" title="Payroll">
                            <span class="nav-link-text">
                                <i class="fas fa-money-check-alt"></i> &nbsp; Payroll
                            </span>
                        </a>
                    </li>
                    @endif

                    @if(auth()->user()->hasPermission('reports', 'view'))
                    {{-- Reports --}}
                    <li class="{{ request()->is('employee-management/reports*') ? 'active' : '' }}">
                        <a href="{{ url('employee-management/reports') }}" title="Reports">
                            <span class="nav-link-text">
                                <i class="fas fa-chart-bar"></i> &nbsp; Reports
                            </span>
                        </a>
                    </li>
                    @endif

                </ul>
            </li>
            @endif

            @if(auth()->user()->hasPermission('additional_service', 'view'))
            <li class="{{ request()->is('additional_services') ? 'active' : '' }}">
                <a href="{{url('/additional_services')}}" title="Additional Service"
                    data-filter-tags="additional_services">
                    <span class="nav-link-text" data-i18n="nav.additionalservices"><i class="fas fa-concierge-bell"></i>
                        &nbsp; Additional Service</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('support_tickets', 'view'))
            <li class="{{ request()->is('tickets*') ? 'active' : '' }}">
                <a href="{{ route('tickets.index') }}" title="Support Tickets" data-filter-tags="support tickets">
                    <span class="nav-link-text"><i class="fas fa-headset"></i>
                        &nbsp; Support Tickets</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('hotel_setup', 'view'))
            <li class="{{ request()->is('hotel/setup*') ? 'active' : '' }}">
                <a href="{{ route('hotel.setup') }}" title="Hotel Setup" data-filter-tags="hotel setup settings">
                    <span class="nav-link-text">
                        <i class="fas fa-sliders-h"></i>
                        &nbsp; Hotel Setup
                    </span>
                </a>
            </li>
            @endif

            @if(auth()->user()->hasPermission('audit_logs', 'view'))
            <li class="{{ request()->is('audit-logs*') ? 'active' : '' }}">
                <a href="{{ url('/audit-logs') }}" title="Audit Logs">
                    <span class="nav-link-text"><i class="fas fa-history"></i>&nbsp; Audit Logs</span>
                </a>
            </li>
            @endif

        </ul>
        <div class="filter-message js-filter-message bg-success-600"></div>
    </nav>
    <!-- END PRIMARY NAVIGATION -->
    <!-- NAV FOOTER -->
    <div class="nav-footer shadow-top">
        <a href="#" onclick="return false;" data-action="toggle" data-class="nav-function-minify"
            class="hidden-md-down">
            <i class="ni ni-chevron-right"></i>
            <i class="ni ni-chevron-right"></i>
        </a>
        <ul class="list-table m-auto nav-footer-buttons">
            <li>
                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Chat logs">
                    <i class="fal fa-comments"></i>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Support Chat">
                    <i class="fal fa-life-ring"></i>
                </a>
            </li>
            <li>
                <a href="javascript:void(0);" data-toggle="tooltip" data-placement="top" title="Make a call">
                    <i class="fal fa-phone"></i>
                </a>
            </li>
        </ul>
    </div> <!-- END NAV FOOTER -->
</aside>