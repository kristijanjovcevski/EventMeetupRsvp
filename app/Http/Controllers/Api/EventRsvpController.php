<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CancelRsvpAction;
use App\Actions\CreateRsvpAction;
use App\Http\Requests\StoreRsvpRequest;
use App\Http\Resources\AttendeeResource;
use App\Http\Resources\RsvpResource;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class EventRsvpController
{
    public function __construct(
        private readonly CreateRsvpAction $createRsvp,
        private readonly CancelRsvpAction $cancelRsvp,
    ) {}

    public function index(Event $event): AnonymousResourceCollection
    {
        $attendees = $event->confirmedRsvps()
            ->with('attendee')
            ->get()
            ->pluck('attendee');

        return AttendeeResource::collection($attendees);
    }

    public function store(StoreRsvpRequest $request, Event $event): JsonResponse
    {
        $attendee = Attendee::query()->findOrFail($request->validated('attendee_id'));

        $rsvp = $this->createRsvp->execute($event, $attendee);

        return RsvpResource::make($rsvp)->response()->setStatusCode(201);
    }

    public function destroy(Event $event, Rsvp $rsvp): Response
    {
        if ($rsvp->event_id !== $event->id) {
            abort(404);
        }

        $this->cancelRsvp->execute($rsvp);

        return response()->noContent();
    }
}
