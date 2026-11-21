<?php

declare(strict_types=1);

namespace App\Actions;

use App\Domain\Enums\RsvpStatus;
use App\Models\Rsvp;

final class CancelRsvpAction
{
    public function execute(Rsvp $rsvp): Rsvp
    {
        if ($rsvp->status === RsvpStatus::Cancelled) {
            return $rsvp;
        }

        $rsvp->update([
            'status'       => RsvpStatus::Cancelled,
            'cancelled_at' => now(),
        ]);

        return $rsvp->refresh();
    }
}
