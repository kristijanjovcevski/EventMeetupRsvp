<?php

use App\Domain\Exceptions\EventConflict;
use App\Models\Attendee;
use App\Models\Event;

it('builds a capacityReached conflict with http 422', function () {
    $event = new Event(['title' => 'Laravel Meetup', 'capacity' => 3]);

    $exception = EventConflict::capacityReached($event);

    expect($exception->getMessage())
        ->toContain('Laravel Meetup')
        ->toContain('3')
        ->toContain('full')
        ->and($exception->httpStatus)->toBe(422);
});

it('builds a duplicateRsvp conflict with http 409', function () {
    $event    = new Event(['title' => 'Laravel Meetup']);
    $attendee = new Attendee(['email' => 'alice@example.com']);

    $exception = EventConflict::duplicateRsvp($event, $attendee);

    expect($exception->getMessage())
        ->toContain('alice@example.com')
        ->toContain('Laravel Meetup')
        ->toContain('already')
        ->and($exception->httpStatus)->toBe(409);
});
