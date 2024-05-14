<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Interaction;

use Afup\Hermes\Repository\Event\FindEventByChannel;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

final readonly class CleaningInteraction implements InteractionInterface
{
    public function __construct(
        private array $adminUserIds,
        private FindEventByChannel $findEventByChannel,
    ) {
    }

    public function callback(Discord $discord): void
    {
        $discord->on(Event::MESSAGE_CREATE, function (Message $message) {
            if (null === $message->author || $message->author->bot) {
                return; // ignore no Author messages & bots
            }

            if (in_array($message->author->id, $this->adminUserIds)) {
                return; // user is admin, let him post
            }

            $event = ($this->findEventByChannel)((int) $message->channel_id);
            if (null === $event) {
                return; // no event in channel, no cleaning is required
            }

            $message->delete();
        });
    }
}
