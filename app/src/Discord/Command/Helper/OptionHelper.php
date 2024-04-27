<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command\Helper;

use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Interactions\Request\Option;

trait OptionHelper
{
    public function getOptionForInteraction(Interaction $interaction, string $option): string|int|float|bool|null
    {
        /** @var Option[] $interactionOptions */
        $interactionOptions = $interaction->data?->options;

        return $interactionOptions[$option]->value;
    }
}
