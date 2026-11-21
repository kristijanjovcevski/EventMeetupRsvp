<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;

final class CreateEventAction
{
    public function execute(array $data): Event
    {
        return Event::query()->create($data);
    }
}
