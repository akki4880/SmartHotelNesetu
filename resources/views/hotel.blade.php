@extends('layouts.master')

@section('title', 'Welcome')

@section('content')
<div class="px-xl-5 px-lg-4 px-3 py-3 page-body">
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="d-flex align-items-center justify-content-between flex-wrap">
                <h3 class="fw-bold mb-0">Hotels</h3>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="row ">
         <!-- 1 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3 ">
                <div class="icon text-primary mb-2"><i class="fas fa-gauge-high"></i></div>
                <h6>Dashboard</h6>
                <div class="right-strip bg-primary"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 2 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-success mb-2"><i class="fas fa-user-tag"></i></div>
                <h6>User Role</h6>
                <div class="right-strip bg-success"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 3 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-danger mb-2"><i class="fas fa-clipboard-list"></i></div>
                <h6>Plans</h6>
                <div class="right-strip bg-danger"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 4 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-warning mb-2"><i class="fas fa-key"></i></div>
                <h6>Permissions</h6>
                <div class="right-strip bg-warning"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 5 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-info mb-2"><i class="fas fa-user-shield"></i></div>
                <h6>Admins</h6>
                <div class="right-strip bg-info"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 6 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-dark mb-2"><i class="fas fa-building"></i></div>
                <h6>Hotels</h6>
                <div class="right-strip bg-dark"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 7 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-secondary mb-2"><i class="fas fa-sliders-h"></i></div>
                <h6>Hotel Setup</h6>
                <div class="right-strip bg-secondary"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 8 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-primary mb-2"><i class="fas fa-layer-group"></i></div>
                <h6>Room Types</h6>
                <div class="right-strip bg-primary"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 9 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-success mb-2"><i class="fas fa-indian-rupee-sign"></i></div>
                <h6>Room Rate Setup</h6>
                <div class="right-strip bg-success"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 10 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-danger mb-2"><i class="fas fa-calendar-alt"></i></div>
                <h6>Seasonal Rate Setup</h6>
                <div class="right-strip bg-danger"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 11 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-warning mb-2"><i class="fas fa-clock"></i></div>
                <h6>Hourly Room Rate Setup</h6>
                <div class="right-strip bg-warning"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 12 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-info mb-2"><i class="fas fa-chart-line"></i></div>
                <h6>Price Chart</h6>
                <div class="right-strip bg-info"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 13 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-dark mb-2"><i class="fas fa-concierge-bell"></i></div>
                <h6>Additional Service</h6>
                <div class="right-strip bg-dark"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 14 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-secondary mb-2"><i class="fas fa-calendar-check"></i></div>
                <h6>Bookings</h6>
                <div class="right-strip bg-secondary"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>

        <!-- 15 -->
        <div class="col-md-3 p-2">
            <div class="custom-card p-3">
                <div class="icon text-primary mb-2"><i class="fas fa-user-friends"></i></div>
                <h6>Guests</h6>
                <div class="right-strip bg-primary"></div>
                <div class="arrow text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>
    </div>
</div>
@endsection