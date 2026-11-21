<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateEventAction;
use App\Actions\DeleteEventAction;
use App\Actions\UpdateEventAction;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class     EventController
{
    public function index(): AnonymousResourceCollection
    {
        $events = Event::query()
            ->withCount('confirmedRsvps')
            ->orderBy('starts_at')
            ->paginate();

        return EventResource::collection($events);
    }

    public function store(StoreEventRequest $request, CreateEventAction $action): JsonResponse
    {
        $event = $action->execute($request->validated());

        return EventResource::make($event)->response()->setStatusCode(201);
    }

    public function show(Event $event): EventResource
    {
        return EventResource::make($event->loadCount('confirmedRsvps'));
    }

    public function update(UpdateEventRequest $request, Event $event, UpdateEventAction $action): EventResource
    {
        return EventResource::make($action->execute($event, $request->validated()));
    }

    public function destroy(Event $event, DeleteEventAction $action): Response
    {
        $action->execute($event);

        return response()->noContent();
    }
}
