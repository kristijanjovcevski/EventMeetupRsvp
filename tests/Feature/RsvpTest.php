<?php

use App\Domain\Enums\RsvpStatus;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Rsvp;

it('creates an RSVP', function () {
    $event    = Event::factory()->capacity(10)->create();
    $attendee = Attendee::factory()->create();

    $this->postJson("/api/events/{$event->id}/rsvps", ['attendee_id' => $attendee->id])
        ->assertCreated()
        ->assertJsonPath('data.event_id', $event->id)
        ->assertJsonPath('data.attendee_id', $attendee->id)
        ->assertJsonPath('data.status', 'confirmed');

    expect(Rsvp::count())->toBe(1);
});

it('rejects a duplicate RSVP with 409', function () {
    $rsvp = Rsvp::factory()->create();

    $this->postJson("/api/events/$rsvp->event_id/rsvps", ['attendee_id' => $rsvp->attendee_id])
        ->assertStatus(409)
        ->assertJsonPath('message', fn ($m) => str_contains($m, 'already'));

    expect(Rsvp::count())->toBe(1);
});

it('rejects an RSVP when the event is at capacity with 422', function () {
    $event = Event::factory()->capacity(2)->create();
    Rsvp::factory()->count(2)->for($event)->create();

    $extra = Attendee::factory()->create();

    $this->postJson("/api/events/{$event->id}/rsvps", ['attendee_id' => $extra->id])
        ->assertStatus(422)
        ->assertJsonPath('message', fn ($m) => str_contains($m, 'full'));

    expect(Rsvp::where('event_id', $event->id)->where('status', 'confirmed')->count())->toBe(2);
});

it('allows a new RSVP after a cancellation frees a seat', function () {
    $event = Event::factory()->capacity(1)->create();
    $rsvp  = Rsvp::factory()->for($event)->create();

    $this->deleteJson("/api/events/{$event->id}/rsvps/{$rsvp->id}")->assertNoContent();

    $newAttendee = Attendee::factory()->create();

    $this->postJson("/api/events/{$event->id}/rsvps", ['attendee_id' => $newAttendee->id])
        ->assertCreated();
});

it('rejects an RSVP with a non-existent attendee', function () {
    $event = Event::factory()->create();

    $this->postJson("/api/events/{$event->id}/rsvps", ['attendee_id' => 9999])
        ->assertStatus(422)
        ->assertJsonValidationErrors('attendee_id');
});

it('returns 404 when the event does not exist', function () {
    $attendee = Attendee::factory()->create();

    $this->postJson('/api/events/9999/rsvps', ['attendee_id' => $attendee->id])
        ->assertNotFound();
});

it('lists only confirmed attendees for an event', function () {
    $event = Event::factory()->create();

    Rsvp::factory()->count(2)->for($event)->create();
    Rsvp::factory()->cancelled()->for($event)->create();

    $this->getJson("/api/events/{$event->id}/rsvps")
        ->assertOk()
        ->assertJsonCount(2, 'data');
});

it('cancels an RSVP idempotently', function () {
    $rsvp = Rsvp::factory()->create();

    $this->deleteJson("/api/events/{$rsvp->event_id}/rsvps/{$rsvp->id}")->assertNoContent();
    $this->deleteJson("/api/events/{$rsvp->event_id}/rsvps/{$rsvp->id}")->assertNoContent();

    expect($rsvp->fresh()->status)->toBe(RsvpStatus::Cancelled)
        ->and($rsvp->fresh()->cancelled_at)->not->toBeNull();
});

it('reactivates a cancelled RSVP without creating a new row', function () {
    $rsvp = Rsvp::factory()->cancelled()->create();

    $this->postJson("/api/events/{$rsvp->event_id}/rsvps", ['attendee_id' => $rsvp->attendee_id])
        ->assertCreated()
        ->assertJsonPath('data.id', $rsvp->id);

    expect(Rsvp::where('event_id', $rsvp->event_id)->where('attendee_id', $rsvp->attendee_id)->count())->toBe(1)
        ->and($rsvp->fresh()->status)->toBe(RsvpStatus::Confirmed)
        ->and($rsvp->fresh()->cancelled_at)->toBeNull();
});

it('returns 404 when cancelling an RSVP that belongs to a different event', function () {
    $rsvp     = Rsvp::factory()->create();
    $otherEvent = Event::factory()->create();

    $this->deleteJson("/api/events/{$otherEvent->id}/rsvps/{$rsvp->id}")
        ->assertNotFound();

    expect($rsvp->fresh()->status)->toBe(RsvpStatus::Confirmed);
});

it('returns 404 when cancelling a missing RSVP', function () {
    $event = Event::factory()->create();

    $this->deleteJson("/api/events/{$event->id}/rsvps/9999")
        ->assertNotFound();
});
