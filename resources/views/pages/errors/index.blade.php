@extends('layouts.master')

@section('title', $title ?? 'Error')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="text-center p-5 ">

            <!-- Error Icon -->
            <i class="fa-solid fa-triangle-exclamation fa-5x text-danger mb-4 animate__animated animate__shakeX"></i>

            <!-- Error Code with blink -->
            <h1 class="display-1 text-danger animate__animated animate__flash animate__infinite">{{ $code ?? 'Error' }}</h1>

            <!-- Error Message -->
            <h3 class="mb-3">{{ $message ?? 'Something went wrong.' }}</h3>

            <!-- Description -->
            <p class="lead">{{ $description ?? '' }}</p>

            <!-- Back Button -->
            <a href="{{ url()->previous() }}" class="btn btn-primary mt-3 btn-lg">
                ⬅ Go Back
            </a>
        </div>
    </div>
</div>
@endsection