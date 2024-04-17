<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Enum\Traveler;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Traveler\GetTravelerListForUserAndEvent;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User as DiscordUser;

final readonly class StatusCommand implements CommandInterface
{
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
            /** @var DiscordUser $discordUser */
            $discordUser = $interaction->user;
            $userId = (int) $discordUser->id;
            $user = ($this->findOrCreateUser)($userId);

            $channelId = (int) $interaction->channel_id;
            $event = ($this->findEventByChannel)($channelId);

            if (null === $event) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: No event found for current channel'), true);

                return;
            }

            $content = sprintf('Your status for "%s" event:', $event->name) . "\n";
            $travelers = ($this->getTravelerListForUserAndEvent)($user, $event);
            foreach ($travelers as $traveler) {
                $status = sprintf('- [%s] Leaving at %s from %s', $traveler->type->value, $traveler->transport->startAt->format(\DateTimeInterface::ATOM), $traveler->transport->postalCode);
                if (Traveler::DRIVER !== $traveler->type) {
                    $status .= sprintf(' (created by <@%d>)', $traveler->transport->getDriver()->userId);
                }

                $content .= $status . "\n";
            }

            $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
        });
    }
}
