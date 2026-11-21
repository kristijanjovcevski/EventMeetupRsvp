<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAttendeeRequest;
use App\Http\Resources\AttendeeResource;
use App\Models\Attendee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AttendeeController
{
    public function index(): AnonymousResourceCollection
    {
        return AttendeeResource::collection(
            Attendee::query()->orderBy('name')->paginate()
        );
    }

    public function store(StoreAttendeeRequest $request): JsonResponse
    {
        $attendee = Attendee::query()->create($request->validated());

        return AttendeeResource::make($attendee)->response()->setStatusCode(201);
    }

    public function show(Attendee $attendee): AttendeeResource
    {
        return AttendeeResource::make($attendee);
    }
}
