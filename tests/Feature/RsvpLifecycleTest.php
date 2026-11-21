<?php

use App\Domain\Enums\RsvpStatus;
use App\Models\Rsvp;

it('runs a complete RSVP lifecycle end-to-end', function () {
    $eventId = $this->postJson('/api/events', [
        'title'       => 'Laravel Meetup',
        'description' => 'Monthly',
        'location'    => 'Skopje',
        'starts_at'   => now()->addDays(10)->format('Y-m-d H:i:s'),
        'capacity'    => 2,
    ])->assertCreated()->json('data.id');

    // 2. Create three attendees
    $alice = $this->postJson('/api/attendees', ['name' => 'Alice', 'email' => 'alice@example.com'])
        ->assertCreated()->json('data.id');
    $bob   = $this->postJson('/api/attendees', ['name' => 'Bob', 'email' => 'bob@example.com'])
        ->assertCreated()->json('data.id');
    $carol = $this->postJson('/api/attendees', ['name' => 'Carol', 'email' => 'carol@example.com'])
        ->assertCreated()->json('data.id');

    // 3. Fill to capacity
    $aliceRsvp = $this->postJson("/api/events/{$eventId}/rsvps", ['attendee_id' => $alice])
        ->assertCreated()->json('data.id');
    $this->postJson("/api/events/{$eventId}/rsvps", ['attendee_id' => $bob])
        ->assertCreated();

    // 4. Capacity and duplicate rejections
    $this->postJson("/api/events/{$eventId}/rsvps", ['attendee_id' => $carol])
        ->assertStatus(422);
    $this->postJson("/api/events/{$eventId}/rsvps", ['attendee_id' => $alice])
        ->assertStatus(409);

    // 5. Cancel Alice's RSVP
    $this->deleteJson("/api/events/{$eventId}/rsvps/{$aliceRsvp}")->assertNoContent();

    // 6. Carol can now RSVP
    $this->postJson("/api/events/{$eventId}/rsvps", ['attendee_id' => $carol])
        ->assertCreated();

    // 7. List shows Bob + Carol
    $this->getJson("/api/events/{$eventId}/rsvps")
        ->assertOk()
        ->assertJsonCount(2, 'data');

    // 8. Final DB state
    expect(Rsvp::where('event_id', $eventId)->where('status', RsvpStatus::Confirmed->value)->count())->toBe(2)
        ->and(Rsvp::where('event_id', $eventId)->where('attendee_id', $alice)->first()->status)
        ->toBe(RsvpStatus::Cancelled);
});

it('cascades RSVPs when the parent event is deleted', function () {
    $rsvp = Rsvp::factory()->create();

    $this->deleteJson("/api/events/{$rsvp->event_id}")->assertNoContent();

    expect(Rsvp::where('event_id', $rsvp->event_id)->count())->toBe(0);
});
