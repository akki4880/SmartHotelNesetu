@extends('layouts.master')

@section('title', 'Invoice Templates')
@section('mainbreadcrumb', 'Invoice Templates')

@section('content')
<style>

.invoice-template-page {
    padding-bottom: 100px !important;
}

.template-card {
    display: block;
    cursor: pointer;
    background: #fff;
    border: 2px solid #edf0f5;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.template-card:hover {
    transform: translateY(-5px);
    border-color: #9b7bea;
    box-shadow: 0 10px 30px rgba(60, 40, 100, 0.12);
}

.template-card.selected {
    border: 2px solid #7652c8;
    box-shadow: 0 0 0 3px rgba(118, 82, 200, 0.12);
}

.template-preview {
    position: relative;
    width: 100%;
    aspect-ratio: 0.72;
    overflow: hidden;
    background: #f5f5f7;
    padding: 8px;
}

.template-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    transition: transform 0.4s ease;
}

.template-card:hover .template-image {
    transform: scale(1.04);
}

.template-hover {
    position: absolute;
    inset: 0;
    background: rgba(20, 25, 45, 0.72);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.template-card:hover .template-hover {
    opacity: 1;
}

.template-type {
    padding: 7px 18px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
}

.template-type.free {
    background: #d1fae5;
    color: #047857;
}

.template-type.paid {
    background: #fff0c2;
    color: #946200;
}

.preview-text {
    color: #fff;
    font-size: 12px;
    margin-top: 15px;
}

.template-details {
    padding: 13px;
}

.template-name {
    font-size: 14px;
    font-weight: 600;
    color: #343047;
}

.template-radio {
    width: 17px;
    height: 17px;
    cursor: pointer;
    accent-color: #7652c8;
    flex-shrink: 0;
}

.selected-indicator {
    position: absolute;
    top: 12px;
    right: 12px;
    width: 30px;
    height: 30px;
    background: #7652c8;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0.7);
    transition: all 0.3s ease;
}

.template-card.selected .selected-indicator {
    opacity: 1;
    transform: scale(1);
}

.template-action-bar {
    position: sticky;
    bottom: 15px;
    z-index: 20;
    margin-top: 30px;
    padding: 16px 22px;
    background: #fff;
    border: 1px solid #e9e5f2;
    border-radius: 12px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.10);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
}

@media(max-width: 576px) {
    .template-action-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .template-action-bar button {
        width: 100%;
    }

    .template-name {
        font-size: 12px;
    }
}

</style>

@php
    $selectedTemplateId = auth()->user()?->hotel?->invoice_template_id ?? null;
@endphp

<div class="container-fluid py-4 invoice-template-page">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Invoice Templates</h4>
            <p class="text-muted mb-0">
                Choose a professional invoice design for your business.
            </p>
@if(auth()->user()->hasPermission('invoice_setup', 'view') || auth()->user()->hasPermission('invoice_setup', 'create') || auth()->user()->hasPermission('invoice_setup', 'edit'))
<div class="container py-4">
    <div class="row g-4">
        @foreach($templates as $template)
        <div class="col-md-4 col-lg-3">
            <div class="card">
                <a href="{{ asset('storage/'.$template->preview_image) }}" data-fancybox="gallery">
                    <img src="{{ asset('storage/'.$template->preview_image) }}" style="width: 264px;">
                </a>
                <div class="template-overlay d-flex align-items-center justify-content-center">
                    <h5 class="text-primary mb-0">{{ $template['name'] }}</h5>
                </div>
            </div>
        </div>

        <span class="badge bg-light text-dark p-2">
            {{ $templates->count() }} Templates
        </span>
    </div>

    <form action="{{ route('invoice-templates.select') }}" method="POST">
        @csrf

        <div class="row g-4">

            @foreach($templates as $template)

            <div class="col-6 col-md-4 col-lg-3 mb-3">

                <label class="template-card
                    {{ ($selectedTemplateId == $template->id) || (!$selectedTemplateId && $template->is_default) ? 'selected' : '' }}"
                    for="template_{{ $template->id }}">

                    {{-- Template Preview --}}
                    <div class="template-preview">

                        <img
                            src="{{ asset('storage/'.$template->preview_image) }}"
                            alt="{{ $template->name }}"
                            class="template-image"
                        >

                        {{-- Hover Overlay --}}
                        <div class="template-hover">

                            @if(strtolower($template->type) == 'free')

                                <span class="template-type free">
                                    <i class="fas fa-gift"></i> FREE
                                </span>

                            @else

                                <span class="template-type paid">
                                    <i class="fas fa-crown"></i> PAID
                                </span>

                                <h5 class="text-white mt-3">
                                    ₹{{ number_format($template->price, 2) }}
                                </h5>

                            @endif

                            <span class="preview-text">
                                <i class="fas fa-search-plus"></i>
                                Preview Template
                            </span>

                        </div>

                        {{-- Selected Indicator --}}
                        <div class="selected-indicator">
                            <i class="fas fa-check"></i>
                        </div>

                    </div>

                    {{-- Template Details --}}
                    <div class="template-details">

                        <div class="d-flex align-items-center gap-2" style="margin-left: 17px;">

                            <input
                                type="radio"
                                name="template_id"
                                value="{{ $template->id }}"
                                id="template_{{ $template->id }}"
                                data-type="{{ strtolower($template->type ?? 'free') }}"
                                {{ ($selectedTemplateId == $template->id) || (!$selectedTemplateId && $template->is_default) ? 'checked' : '' }}
                                class="form-check-input template-radio"
                            >

                            <span class="template-name">
                                {{ $template->name }}
                            </span>

                        </div>

                        <small class="text-muted">
                            {{ ucfirst($template->type) }} Template
                        </small>

                    </div>

                </label>

            </div>

            @endforeach

        </div>

        {{-- Bottom Action --}}
        <div class="template-action-bar">

            <div>
                <span class="fw-semibold">Template Selection</span>
                <p class="text-muted mb-0 small">
                    Select one template to use for your invoices.
                </p>
            </div>

            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-check-circle me-2"></i>
                Use Selected Template
            </button>

        </div>

    </form>

</div>
<script>

document.querySelectorAll('.template-radio').forEach(radio => {

    radio.addEventListener('change', function () {

        if ((this.dataset.type || 'free') === 'paid') {
            this.checked = false;
            alert('This is a paid template. Please contact Smart Hotel to unlock it.');
            return;
        }

        document.querySelectorAll('.template-card').forEach(card => {
            card.classList.remove('selected');
        });

        this.closest('.template-card').classList.add('selected');

    });

    if (radio.checked) {
        radio.closest('.template-card')?.classList.add('selected');
    }

});

</script>
@else
<div class="alert alert-warning">You do not have permission to access invoice setup.</div>
@endif
@endsection