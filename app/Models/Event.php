<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Event extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'location', 'starts_at', 'capacity'];

    protected function casts(): array
    {
        return ['starts_at' => 'immutable_datetime', 'capacity' => 'integer'];
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function confirmedRsvps(): HasMany
    {
        return $this->rsvps()->where('status', 'confirmed');
    }
}
