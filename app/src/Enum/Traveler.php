<?php

declare(strict_types=1);

namespace Afup\Hermes\Enum;

enum Traveler: string
{
    case DRIVER = 'driver';
    case PASSENGER = 'passenger';
}
