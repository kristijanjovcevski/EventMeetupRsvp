<?php

declare(strict_types=1);
namespace App\Domain\Enums;
enum RsvpStatus: string
{
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
}
