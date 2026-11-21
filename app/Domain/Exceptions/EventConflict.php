<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

use App\Models\Attendee;
use App\Models\Event;
use RuntimeException;
final class EventConflict extends RuntimeException
{
    private function __construct(
        string $message,
        public readonly int $httpStatus,
    ) {
        parent::__construct($message);
    }

    public static function capacityReached(Event $event): self
    {
        return new self(
            sprintf("Event '%s' is full (capacity: %d).", $event->title, $event->capacity),
            422,
        );
    }

    public static function duplicateRsvp(Event $event, Attendee $attendee): self
    {
        return new self(
            sprintf("Attendee '%s' has already RSVP'd to event '%s'.", $attendee->email, $event->title),
            409,
        );
    }
}
