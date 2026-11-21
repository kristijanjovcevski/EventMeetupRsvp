<?php

use App\Models\Event;
use App\Models\Rsvp;

it('creates an event', function () {
    $this->postJson('/api/events', [
        'title'       => 'Laravel Meetup',
        'description' => 'Monthly meetup',
        'location'    => 'Skopje',
        'starts_at'   => now()->addDays(7)->format('Y-m-d H:i:s'),
        'capacity'    => 10,
    ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Laravel Meetup')
        ->assertJsonPath('data.capacity', 10)
        ->assertJsonPath('data.description', 'Monthly meetup');

    expect(Event::count())->toBe(1);
});

it('creates an event without a description', function () {
    $this->postJson('/api/events', [
        'title'     => 'Minimal Event',
        'location'  => 'Skopje',
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'capacity'  => 5,
    ])
        ->assertCreated()
        ->assertJsonPath('data.description', null);
});

it('rejects an event with a past starts_at', function () {
    $this->postJson('/api/events', [
        'title'     => 'Past Event',
        'location'  => 'Skopje',
        'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
        'capacity'  => 5,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('starts_at');
});

it('rejects an event with capacity below 1', function () {
    $this->postJson('/api/events', [
        'title'     => 'Zero Capacity',
        'location'  => 'Skopje',
        'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'capacity'  => 0,
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('capacity');
});

it('rejects an event with missing required fields', function () {
    $this->postJson('/api/events', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'location', 'starts_at', 'capacity']);
});

it('lists events paginated', function () {
    Event::factory()->count(3)->create();

    $this->getJson('/api/events')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3);
});

it('shows a single event with confirmed count', function () {
    $event = Event::factory()->capacity(5)->create(['title' => 'Specific Event']);

    Rsvp::factory()->count(2)->for($event)->create();

    $this->getJson("/api/events/$event->id")
        ->assertOk()
        ->assertJsonPath('data.title', 'Specific Event')
        ->assertJsonPath('data.confirmed_count', 2);
});

it('returns 404 for a missing event', function () {
    $this->getJson('/api/events/9999')->assertNotFound();
});

it('updates an event partially', function () {
    $event = Event::factory()->capacity(5)->create(['title' => 'Old']);

    $this->patchJson("/api/events/$event->id", ['title' => 'New'])
        ->assertOk()
        ->assertJsonPath('data.title', 'New')
        ->assertJsonPath('data.capacity', 5);

    expect($event->fresh()->title)->toBe('New');
});

it('rejects an update with invalid capacity', function () {
    $event = Event::factory()->create();

    $this->patchJson("/api/events/$event->id", ['capacity' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors('capacity');
});

it('deletes an event', function () {
    $event = Event::factory()->create();

    $this->deleteJson("/api/events/$event->id")->assertNoContent();

    expect(Event::find($event->id))->toBeNull();
});

it('cascades RSVP deletion when an event is deleted', function () {
    $event = Event::factory()->create();
    Rsvp::factory()->count(2)->for($event)->create();

    expect(Rsvp::where('event_id', $event->id)->count())->toBe(2);

    $this->deleteJson("/api/events/$event->id")->assertNoContent();

    expect(Rsvp::where('event_id', $event->id)->count())->toBe(0);
});
