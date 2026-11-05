@extends('layouts.master')

@section('title', 'Hotel Setup')
@section('mainbreadcrumb', 'Hotel Setup')

@section('content')
@if(auth()->user()->hasPermission('hotel_setup', 'view') || auth()->user()->hasPermission('hotel_setup', 'edit'))
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Hotel Setup</h3>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0">
                    <div class="panel-container show">
                        <div class="panel-content">
                            <div class="row">
                                <div class="col-auto">
                                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                                        @if(auth()->user()->hasPermission('hotel_setup', 'view') || auth()->user()->hasPermission('hotel_setup', 'edit'))
                                        <a class="nav-link active"
                                            id="hotel-basic-tab"
                                            data-toggle="pill"
                                            href="#hotel-basic"
                                            role="tab">
                                            <i class="fal fa-hotel"></i>
                                            <span class="hidden-sm-down ml-1"> Basic Info</span>
                                        </a>

                                        <a class="nav-link"
                                            id="hotel-operations-tab"
                                            data-toggle="pill"
                                            href="#hotel-operations"
                                            role="tab">
                                            <i class="fal fa-clock"></i>
                                            <span class="hidden-sm-down ml-1"> Operations</span>
                                        </a>

                                        <a class="nav-link"
                                            id="hotel-pricing-tab"
                                            data-toggle="pill"
                                            href="#hotel-pricing"
                                            role="tab">
                                            <i class="fal fa-rupee-sign"></i>
                                            <span class="hidden-sm-down ml-1"> Pricing Setup</span>
                                        </a>

                                        <a class="nav-link"
                                            id="hotel-booking-tab"
                                            data-toggle="pill"
                                            href="#hotel-booking"
                                            role="tab">
                                            <i class="fal fa-receipt"></i>
                                            <span class="hidden-sm-down ml-1"> Booking Settings</span>
                                        </a>

                                        <a class="nav-link"
                                            id="hotel-tax-tab"
                                            data-toggle="pill"
                                            href="#hotel-tax"
                                            role="tab">
                                            <i class="fal fa-file-invoice"></i>
                                            <span class="hidden-sm-down ml-1"> Tax & Billing</span>
                                        </a>
                                        <a class="nav-link"
                                            id="hotel-po-tab"
                                            data-toggle="pill"
                                            href="#hotel-po"
                                            role="tab">
                                            <i class="fal fa-file-invoice-dollar"></i>
                                            <span class="hidden-sm-down ml-1"> PO Settings</span>
                                        </a>
                                        @endif

                                    </div>
                                </div>
                                <div class="col" style=" border-left:2px solid #d8d8d8;  ">
                                    <div class="tab-content" id="v-pills-tabContent">
                                        @if(auth()->user()->hasPermission('hotel_setup', 'view') || auth()->user()->hasPermission('hotel_setup', 'edit'))
                                        <div class="tab-pane fade show active"
                                            id="hotel-basic"
                                            role="tabpanel">
                                            {{-- Basic Info Form --}}
                                            @include('pages.hotelsetup.partials.basic-info')
                                        </div>

                                        <div class="tab-pane fade"
                                            id="hotel-operations"
                                            role="tabpanel">
                                            {{-- Check-in/out, timezone --}}
                                            @include('pages.hotelsetup.partials.operations')
                                        </div>

                                        <div class="tab-pane fade"
                                            id="hotel-pricing"
                                            role="tabpanel">
                                            {{-- Room rate / Hourly rate toggle --}}
                                            @include('pages.hotelsetup.partials.pricing')
                                        </div>

                                        <div class="tab-pane fade"
                                            id="hotel-booking"
                                            role="tabpanel">

                                            @include('pages.hotelsetup.partials.booking-settings')

                                        </div>

                                        <div class="tab-pane fade"
                                            id="hotel-tax"
                                            role="tabpanel">
                                            {{-- GST, PAN, Invoice prefix --}}
                                            @include('pages.hotelsetup.partials.tax-billing')
                                        </div>

                                        <div class="tab-pane fade"
                                            id="hotel-po"
                                            role="tabpanel">

                                            @include('pages.hotelsetup.partials.po-settings')

                                        </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="alert alert-warning">You do not have permission to access Hotel Setup.</div>
@endif

@endsection