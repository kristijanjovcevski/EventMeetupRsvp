<?php

use App\Models\Attendee;

it('creates an attendee', function () {
    $this->postJson('/api/attendees', [
        'name'  => 'Alice',
        'email' => 'alice@example.com',
    ])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Alice')
        ->assertJsonPath('data.email', 'alice@example.com');

    expect(Attendee::count())->toBe(1);
});

it('normalizes the email to lowercase on create', function () {
    $this->postJson('/api/attendees', [
        'name'  => 'Alice',
        'email' => 'ALICE@Example.COM',
    ])->assertCreated();

    expect(Attendee::first()->email)->toBe('alice@example.com');
});

it('rejects a duplicate email', function () {
    Attendee::factory()->create(['email' => 'alice@example.com']);

    $this->postJson('/api/attendees', [
        'name'  => 'Alice Again',
        'email' => 'alice@example.com',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('rejects a duplicate email in a different case', function () {
    Attendee::factory()->create(['email' => 'alice@example.com']);

    $this->postJson('/api/attendees', [
        'name'  => 'Alice Again',
        'email' => 'ALICE@example.com',
    ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('rejects an invalid email', function () {
    $this->postJson('/api/attendees', ['name' => 'X', 'email' => 'not-an-email'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('email');
});

it('rejects a missing name', function () {
    $this->postJson('/api/attendees', ['email' => 'x@example.com'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('name');
});

it('lists attendees paginated', function () {
    Attendee::factory()->count(3)->create();

    $this->getJson('/api/attendees')
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('meta.total', 3);
});

it('shows a single attendee', function () {
    $attendee = Attendee::factory()->create(['name' => 'Specific']);

    $this->getJson("/api/attendees/{$attendee->id}")
        ->assertOk()
        ->assertJsonPath('data.name', 'Specific');
});

it('returns 404 for a missing attendee', function () {
    $this->getJson('/api/attendees/9999')->assertNotFound();
});
