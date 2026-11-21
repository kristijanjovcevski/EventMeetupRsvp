<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class EventResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'title'         => $this->title,
            'description'   => $this->description,
            'location'      => $this->location,
            'starts_at'     => $this->starts_at->toIso8601String(),
            'capacity'      => $this->capacity,
            'confirmed_count' => $this->whenCounted('confirmedRsvps'),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
