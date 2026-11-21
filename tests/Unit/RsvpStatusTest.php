<?php

use App\Domain\Enums\RsvpStatus;

it('has exactly the confirmed and cancelled cases', function () {
    expect(RsvpStatus::cases())->toHaveCount(2)
        ->and(RsvpStatus::Confirmed->value)->toBe('confirmed')
        ->and(RsvpStatus::Cancelled->value)->toBe('cancelled');
});

it('is a backed string enum', function () {
    expect(RsvpStatus::Confirmed)->toBeInstanceOf(BackedEnum::class)
        ->and(RsvpStatus::from('confirmed'))->toBe(RsvpStatus::Confirmed)
        ->and(RsvpStatus::tryFrom('bogus'))->toBeNull();
});
