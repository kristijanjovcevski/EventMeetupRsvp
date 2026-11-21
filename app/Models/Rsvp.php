<?php

namespace App\Models;

use App\Domain\Enums\RsvpStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Rsvp extends Model
{
    use HasFactory;

    protected $fillable = ['event_id', 'attendee_id', 'status', 'cancelled_at'];

    protected function casts(): array
    {
        return ['status' => RsvpStatus::class, 'cancelled_at' => 'immutable_datetime'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }
}
