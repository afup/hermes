<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Interaction;

use Discord\Discord;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface InteractionInterface
{
    public function callback(Discord $discord): void;
}
