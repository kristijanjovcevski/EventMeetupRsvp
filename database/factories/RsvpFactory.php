<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Enums\RsvpStatus;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Database\Eloquent\Factories\Factory;

class RsvpFactory extends Factory
{
    protected $model = Rsvp::class;

    public function definition(): array
    {
        return [
            'event_id'     => Event::factory(),
            'attendee_id'  => Attendee::factory(),
            'status'       => RsvpStatus::Confirmed,
            'cancelled_at' => null,
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'       => RsvpStatus::Cancelled,
            'cancelled_at' => now(),
        ]);
    }
}
