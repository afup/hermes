<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Interaction;

use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Event;

final readonly class CleaningInteraction implements InteractionInterface
{
    public function __construct(
        private array $adminUserIds,
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

            $message->delete();
        });
    }
}
