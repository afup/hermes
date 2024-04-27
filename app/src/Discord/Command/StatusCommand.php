<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\UserHelper;
use Afup\Hermes\Enum\Traveler;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Traveler\GetTravelerListForUserAndEvent;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;

final readonly class StatusCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;

    private const COMMAND_NAME = 'status';

    public function __construct(
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private GetTravelerListForUserAndEvent $getTravelerListForUserAndEvent,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription('Your current status within the current channel event');
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) {
            if ($interaction->user?->bot ?? false) {
                return; // ignore bots
            }

            if (false === ($event = $this->getEventForInteraction($interaction))) {
                return;
            }
            $user = $this->getUserForInteraction($interaction);

            $content = sprintf('Your status for "%s" event:', $event->name) . "\n";
            $travelers = ($this->getTravelerListForUserAndEvent)($user, $event);
            $hasContent = false;
            foreach ($travelers as $traveler) {
                $status = sprintf('- [%s] Leaving at %s from %s', $traveler->type->value, $traveler->transport->startAt->format(\DateTimeInterface::ATOM), $traveler->transport->postalCode);
                if (Traveler::DRIVER !== $traveler->type) {
                    $status .= sprintf(' (created by <@%d>)', $traveler->transport->getDriver()->userId);
                }

                $content .= $status . "\n";
                $hasContent = true;
            }

            if (!$hasContent) {
                $content = sprintf('You have not registered in any transport for "%s" event.', $event->name);
            }

            $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
        });
    }
}
