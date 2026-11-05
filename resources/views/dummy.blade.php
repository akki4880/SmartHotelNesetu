@extends('layouts.master')

@section('title', '')
@section('mainbreadcrumb', '')

@section('content')
@php
// Get all request parameters except _token or page
$filters = request()->except(['_token', 'page']);

// Check if any filter has a non-empty value
$hasFilter = collect($filters)->filter(fn($value) => !empty($value))->isNotEmpty();
@endphp
<div class="row" style="{{ $hasFilter ? '' : 'display:none;' }}" id="filterRow">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header bg-info d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder text-white"> <i class="fa-solid fa-filter"></i> Filter</h3>
            </div>
            <div class="card-body">
                <!-- FILTER FORM (HIDDEN BY DEFAULT) -->
                <form method="GET" action="{{ url()->current() }}">
                    <div class="row"></div>

                </form>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="card mb-g">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="mb-0 fw-bolder">Plans</h3>
                <div class="float-right">
                    <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#createModal">
                        <i class="fa-solid fa-plus"></i> Create
                    </button>
                    <button id="filterBtn" class="btn btn-info btn-sm" onclick="toggleFilter()">
                        <i class="fa-solid fa-filter"></i> Filter
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="frame-wrap p-0 border-0 m-0" id="table-container">
                    <table class="table m-0 table-bordered table-sm" id="table-example">
                        <thead class="bg-primary text-white">
                            <tr>
                                
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Create Modal --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h4 class="modal-title">Create</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="CreateForm">
                    @csrf
                    <div class="row"></div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" id="clearAddBtn">Clear</button>
                <button type="button" class="btn btn-danger" id="saveBtn">Save</button>
            </div>
        </div>
    </div>
</div>


{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h4 class="modal-title">Edit</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="fal fa-times"></i></span>
                </button>
            </div>

            <div class="modal-body">
                <form id="EditForm">
                    @csrf
                    <input type="hidden" id="" name="">

                    <div class="row"></div>
                </form>
            </div>

            <div class="modal-footer border-top">
                <button type="reset" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="updateBtn">Update</button>
            </div>
        </div>
    </div>
</div>

<script>
    
</script>

@endsection