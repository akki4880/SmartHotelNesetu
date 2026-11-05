@extends('layouts.master')

@section('title', 'House Keeping')

@section('content')
@php
    $statusLabels = [
        0 => 'Ready',
        1 => 'Booked',
        2 => 'Maintenance',
        3 => 'Dirty',
        4 => 'Cleaning',
        5 => 'Blocked',
    ];
@endphp

<style>
    .hk-room-section {
        margin-bottom: 30px;
        padding: 18px 18px 16px;
        background: linear-gradient(180deg, rgba(255,255,255,0.9) 0%, rgba(245,247,251,0.9) 100%);
        border: 1px solid #e7edf5;
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(16, 24, 40, 0.04);
    }

    .hk-room-type-title {
        margin: 0 0 16px;
        padding: 10px 16px;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 20px;
        font-weight: 800;
        color: #1d2433;
        background: linear-gradient(135deg, #f0f4ff 0%, #eef8ff 100%);
        border: 1px solid #dfe9fb;
        border-radius: 12px;
        letter-spacing: 0.2px;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    }

    .hk-room-type-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        border-radius: 9px;
        background: rgba(79, 70, 229, 0.06);
        border: 1.5px solid rgba(79, 70, 229, 0.28);
        color: #4f46e5;
        font-size: 18px;
        line-height: 1;
        font-weight: 700;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.6);
    }

    .hk-room-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(180px, 1fr));
        gap: 16px;
    }

    .hk-room-card {
        position: relative;
        background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
        border: 1px solid #dde5f0;
        border-radius: 16px;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.04);
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .hk-room-card::before {
        content: "";
        position: absolute;
        inset: 0 auto auto 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #4f46e5, #06b6d4);
        opacity: 0;
        transition: opacity 0.2s ease;
    }

    .hk-room-card:hover {
        border-color: #c8d7f0;
        transform: translateY(-2px);
        box-shadow: 0 14px 26px rgba(31, 55, 85, 0.08);
    }

    .hk-room-card:hover::before {
        opacity: 1;
    }

    .hk-room-card-inner {
        width: 100%;
        height: 100%;
        padding: 14px 12px 12px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .hk-room-toggle {
        position: absolute;
        top: 12px;
        right: 12px;
    }

    .hk-room-switch {
        position: relative;
        display: inline-block;
        width: 38px;
        height: 22px;
        margin: 0;
    }

    .hk-room-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .hk-room-slider {
        position: absolute;
        inset: 0;
        background: #eef2f7;
        border: 1px solid #d6dfeb;
        border-radius: 999px;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .hk-room-slider::before {
        content: "";
        position: absolute;
        width: 12px;
        height: 12px;
        left: 4px;
        top: 4px;
        border-radius: 50%;
        background: #a1afc2;
        transition: all 0.2s ease;
    }

    .hk-room-switch input:checked + .hk-room-slider {
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        border-color: #4f46e5;
    }

    .hk-room-switch input:checked + .hk-room-slider::before {
        transform: translateX(16px);
        background: #ffffff;
    }

    .hk-room-number {
        color: #3b2f4d;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.3;
        margin-top: 10px;
        text-align: center;
    }

    .hk-room-status {
        color: #516071;
        font-size: 13px;
        line-height: 1.3;
        margin-top: 4px;
        text-align: center;
        font-weight: 600;
        letter-spacing: 0.2px;
    }

    .hk-room-card[data-status="1"] .hk-room-toggle {
        display: none;
    }

    .hk-room-card[data-status="3"] .hk-room-toggle {
        opacity: 0.7;
    }

    .hk-room-card[data-status="0"] .hk-room-status,
    .hk-room-card[data-status="4"] .hk-room-status,
    .hk-room-card[data-status="3"] .hk-room-status,
    .hk-room-card[data-status="5"] .hk-room-status {
        color: #5f6f81;
    }

    @media (max-width: 991.98px) {
        .hk-room-grid {
            grid-template-columns: repeat(3, minmax(180px, 1fr));
        }
    }

    @media (max-width: 767.98px) {
        .hk-room-grid {
            grid-template-columns: repeat(2, minmax(160px, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .hk-room-grid {
            grid-template-columns: 1fr;
        }

        .hk-room-type-title {
            font-size: 18px;
            width: 100%;
            justify-content: flex-start;
        }
    }
</style>

<div class="container-fluid">
    @forelse ($roomTypes as $roomType)
        @if ($roomType->roomNumbers->isNotEmpty())
            <div class="hk-room-section">
                <h4 class="hk-room-type-title"><span class="hk-room-type-icon">⌂</span>{{ $roomType->name }}</h4>

                <div class="hk-room-grid">
                    @foreach ($roomType->roomNumbers as $room)
                        @php
                            $roomStatus = $statusLabels[$room->status] ?? 'Ready';
                            $allowedToggleStates = [0, 2, 3, 4, 5];
                            $isReady = (int) $room->status === 0;
                            $isChecked = $isReady;
                        @endphp

                        <div class="hk-room-card" data-status="{{ (int) $room->status }}" data-room-number="{{ $room->room_number }}" data-room-id="{{ $room->id }}">
                            <div class="hk-room-card-inner">
                                <div class="hk-room-toggle">
                                    <label class="hk-room-switch">
                                        <input type="checkbox" aria-label="Toggle room {{ $room->room_number }}" {{ $isChecked ? 'checked' : '' }} {{ !in_array((int) $room->status, $allowedToggleStates, true) ? 'disabled' : '' }}>
                                        <span class="hk-room-slider"></span>
                                    </label>
                                </div>

                                <div class="hk-room-number">Room No.{{ $room->room_number }}</div>
                                <div class="hk-room-status">{{ $roomStatus }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @empty
        <div class="alert alert-info">No rooms found for your hotel.</div>
    @endforelse
</div>

<link rel="stylesheet" href="{{ asset('assets/css/notifications/sweetalert2/sweetalert2.bundle.css') }}">
<script src="{{ asset('assets/js/notifications/sweetalert2/sweetalert2.bundle.js') }}"></script>
<script>
    document.addEventListener('change', function (event) {
        const input = event.target;
        if (!input.matches('.hk-room-switch input')) return;

        if (typeof Swal === 'undefined') {
            console.error('SweetAlert2 is not loaded.');
            return;
        }

        const card = input.closest('.hk-room-card');
        const roomNumber = card.dataset.roomNumber || 'this room';
        const roomId = Number(card.dataset.roomId);
        const currentStatus = Number(card.dataset.status);

        if ([2, 3, 4, 5].includes(currentStatus)) {
            Swal.fire({
                title: 'Make room ready?',
                text: 'Make Room No.' + roomNumber + ' ready to use?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, ready',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    input.checked = false;
                    return;
                }

                updateRoomStatus(roomId, 0, card, roomNumber, input, 'Ready');
            });
            return;
        }

        if (currentStatus === 0) {
            Swal.fire({
                title: 'Change room status',
                text: 'Select the new status for Room No.' + roomNumber,
                icon: 'info',
                input: 'select',
                inputOptions: {
                    '0': 'Ready',
                    '3': 'Dirty',
                    '4': 'Cleaning',
                    '2': 'Maintenance',
                    '5': 'Blocked'
                },
                inputValue: '0',
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                inputValidator: (value) => {
                    if (!value) {
                        return 'Please select a status';
                    }
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    input.checked = true;
                    return;
                }

                const selectedStatus = Number(result.value);
                updateRoomStatus(roomId, selectedStatus, card, roomNumber, input, selectedStatus === 0 ? 'Ready' : (selectedStatus === 3 ? 'Dirty' : (selectedStatus === 4 ? 'Cleaning' : (selectedStatus === 2 ? 'Maintenance' : 'Blocked'))));
            });
            return;
        }

        input.checked = false;
    });

    function updateRoomStatus(roomId, status, card, roomNumber, input, label) {
        fetch('{{ route('housekeeping.room.status') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                room_id: roomId,
                status: status
            })
        })
        .then(async (response) => {
            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Failed to update room status.');
            }

            card.dataset.status = String(status);
            card.querySelector('.hk-room-status').textContent = label;

            const isReady = Number(status) === 0;
            input.checked = isReady;
            input.disabled = false;

            if (status === 3) {
                input.checked = false;
            }

            Swal.fire({
                title: 'Room updated',
                text: 'Room No.' + roomNumber + ' is now ' + label + '.',
                icon: 'success',
                confirmButtonText: 'OK'
            });
        })
        .catch((error) => {
            input.checked = (Number(card.dataset.status) === 0);
            Swal.fire({
                title: 'Update failed',
                text: error.message || 'Unable to update room status.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    }
</script>
@endsection