<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Event;

final class UpdateEventAction
{
    public function execute(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->refresh();
    }
}
