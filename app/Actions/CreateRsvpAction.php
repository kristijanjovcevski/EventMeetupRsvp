<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Enums\RsvpStatus;
use App\Domain\Exceptions\EventConflict;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Support\Facades\DB;

final class CreateRsvpAction
{
    public function execute(Event $event, Attendee $attendee): Rsvp
    {
        return DB::transaction(function () use ($event, $attendee) {

            $locked = Event::query()
                ->lockForUpdate()
                ->findOrFail($event->getKey());

            $existing = Rsvp::query()
                ->where('event_id', $locked->getKey())
                ->where('attendee_id', $attendee->getKey())
                ->first();

            if ($existing && $existing->status === RsvpStatus::Confirmed) {
                throw EventConflict::duplicateRsvp($locked, $attendee);
            }

            $confirmedCount = Rsvp::query()
                ->where('event_id', $locked->getKey())
                ->where('status', RsvpStatus::Confirmed->value)
                ->count();

            if ($confirmedCount >= $locked->capacity) {
                throw EventConflict::capacityReached($locked);
            }

            if ($existing) {
                $existing->update([
                    'status'       => RsvpStatus::Confirmed,
                    'cancelled_at' => null,
                ]);

                return $existing->refresh();
            }

            return Rsvp::query()->create([
                'event_id'    => $locked->getKey(),
                'attendee_id' => $attendee->getKey(),
                'status'      => RsvpStatus::Confirmed,
            ]);
        });
    }
}
