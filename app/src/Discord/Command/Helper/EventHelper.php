<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command\Helper;

use Afup\Hermes\Entity\Event;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;

trait EventHelper
{
    public function getEventForInteraction(Interaction $interaction): false|Event
    {
        $channelId = (int) $interaction->channel_id;
        $event = ($this->findEventByChannel)($channelId);

        if (null === $event) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: No event found for current channel'), true);

            return false;
        }

        return $event;
    }
}
