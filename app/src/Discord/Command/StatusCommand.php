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
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class StatusCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;

    private const COMMAND_NAME = 'status';

    public function __construct(
        private TranslatorInterface $translator,
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private GetTravelerListForUserAndEvent $getTravelerListForUserAndEvent,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription($this->translator->trans('discord.status.description'));
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) {
            if (null === $interaction->user || $interaction->user->bot) {
                return; // ignore bots
            }

            if (false === ($event = $this->getEventForInteraction($interaction))) {
                return;
            }
            $user = $this->getUserForInteraction($interaction);

            $content = $this->translator->trans('discord.status.intro', ['name' => $event->name]) . "\n";
            $travelers = ($this->getTravelerListForUserAndEvent)($user, $event);
            $hasContent = false;
            foreach ($travelers as $traveler) {
                $status = $this->translator->trans('discord.status.row', ['transport_id' => $traveler->transport->shortId, 'traveler_type' => $traveler->type->value, 'hour' => $traveler->transport->startAt->format('H\hi'), 'date' => $traveler->transport->startAt->format('j F Y'), 'postal_code' => $traveler->transport->postalCode]);
                if (Traveler::DRIVER !== $traveler->type) {
                    $status .= $this->translator->trans('discord.status.row_not_driver', ['driver_id' => $traveler->transport->getDriver()->userId]);
                } else {
                    $sharedWith = [];
                    $passengerCount = 0;
                    foreach ($traveler->transport->getPassengers() as $passenger) {
                        $sharedWith[] = sprintf('<@%d>', $passenger->user->userId);
                        ++$passengerCount;
                    }

                    $translationKey = 'discord.status.row_driver_no_passengers';
                    $translationData = ['seats_remaining' => $traveler->transport->availableSeats(), 'seats_total' => $traveler->transport->seats];
                    if ($passengerCount > 0) {
                        $translationKey = 'discord.status.row_driver';
                        $translationData['travelers'] = implode(', ', $sharedWith);
                    }

                    $status .= $this->translator->trans($translationKey, $translationData);
                }

                $content .= $status . "\n";
                $hasContent = true;
            }

            if (!$hasContent) {
                $content = $this->translator->trans('discord.status.empty', ['name' => $event->name]);
            }

            $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
        });
    }
}
