<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Discord\Builders\CommandBuilder;
use Discord\Discord;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag]
interface CommandInterface
{
    public function configure(Discord $discord): CommandBuilder;

    public function callback(Discord $discord): void;
}
