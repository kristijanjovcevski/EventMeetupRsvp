<?php

declare(strict_types=1);

use App\Http\Controllers\Api\AttendeeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\EventRsvpController;
use Illuminate\Support\Facades\Route;

Route::apiResource('events', EventController::class);
Route::apiResource('attendees', AttendeeController::class)->only(['index', 'store', 'show']);

Route::prefix('events/{event}')->group(function () {
    Route::get('rsvps',                 [EventRsvpController::class, 'index']);   // list attendees
    Route::post('rsvps',                [EventRsvpController::class, 'store']);   // RSVP
    Route::delete('rsvps/{rsvp}',       [EventRsvpController::class, 'destroy']); // cancel
});
