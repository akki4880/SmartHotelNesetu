<?php

use App\Models\Hotel;

it('supports room and hourly rate modes when configured', function () {
    $hotel = new Hotel([
        'room_rate_type' => ['room', 'hourly'],
    ]);

    expect($hotel->supportsRoomRate())->toBeTrue()
        ->and($hotel->supportsHourlyRate())->toBeTrue();
});

it('defaults to the enabled rate mode when only one option is available', function () {
    $hotel = new Hotel([
        'room_rate_type' => ['hourly'],
    ]);

    expect($hotel->getDefaultBookingRateType())->toBe('hourly');
});

it('falls back to room rate when room pricing is enabled', function () {
    $hotel = new Hotel([
        'room_rate_type' => ['room'],
    ]);

    expect($hotel->getDefaultBookingRateType())->toBe('room');
});
