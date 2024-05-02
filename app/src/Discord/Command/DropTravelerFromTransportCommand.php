<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\OptionHelper;
use Afup\Hermes\Discord\Command\Helper\UserHelper;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\FindUserTransportsForEvent;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DropTravelerFromTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'drop_traveler_from_transport';

    public function __construct(
        private TranslatorInterface $translator,
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private FindUserTransportsForEvent $findUserTransportForEvent,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription($this->translator->trans('discord.drop_traveler_from_transport.description'));
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) use ($discord) {
            if (null === $interaction->user || $interaction->user->bot) {
                return; // ignore bots
            }

            if (false === ($event = $this->getEventForInteraction($interaction))) {
                return;
            }
            $user = $this->getUserForInteraction($interaction);

            $transports = ($this->findUserTransportForEvent)($event, $user);
            if (0 === \count($transports)) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.drop_traveler_from_transport.error.no_transport')), true);

                return;
            }

            if (1 === \count($transports)) {
                $this->chooseTravelerToDrop($discord, $interaction, $transports[0]);
            } else {
                $embed = new Embed($discord);
                $embed->setTitle($this->translator->trans('discord.drop_traveler_from_transport.ask_transport'));
                $message = MessageBuilder::new()->addEmbed($embed);

                $chunkedTransports = array_chunk($transports, 5);
                foreach ($chunkedTransports as $transportRow) {
                    $chooseAction = ActionRow::new();

                    foreach ($transportRow as $transport) {
                        $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.drop_traveler_from_transport.transport_button', ['direction' => Direction::EVENT === $transport->direction ? $this->translator->trans('enum.event') : $this->translator->trans('enum.home'), 'date' => $transport->startAt->format('H\hi \o\n j F Y')]))->setEmoji('🚗')->setListener(function (Interaction $interaction) use ($discord, $transport): void {
                            $this->chooseTravelerToDrop($discord, $interaction, $transport);
                        }, $discord));
                    }

                    $message->addComponent($chooseAction);
                }

                $interaction->respondWithMessage($message, true);
            }
        });
    }

    private function chooseTravelerToDrop(Discord $discord, Interaction $interaction, Transport $transport): void
    {
        $embed = new Embed($discord);
        $embed->setTitle($this->translator->trans('discord.drop_traveler_from_transport.ask_traveler'));
        $message = MessageBuilder::new()->addEmbed($embed);

        if (0 === \count($passengers = iterator_to_array($transport->getPassengers()))) {
            $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.drop_traveler_from_transport.no_traveler'))->setComponents([])->setEmbeds([]));

            return;
        }

        /** @var array<array<Traveler>> $chunkedTravelers */
        $chunkedTravelers = array_chunk($passengers, 5);
        foreach ($chunkedTravelers as $travelerRow) {
            $chooseAction = ActionRow::new();

            foreach ($travelerRow as $traveler) {
                $discord->users->fetch((string) $traveler->user->userId)->then(function (User $travelerUser) use ($chooseAction, $discord, $traveler) {
                    $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.drop_traveler_from_transport.traveler_button', ['traveler_display_name' => $travelerUser->displayname]))->setEmoji('👤')->setListener(function (Interaction $interaction) use ($discord, $traveler, $travelerUser): void {
                        $this->validateTravelerToDrop($discord, $interaction, $traveler, $travelerUser);
                    }, $discord));
                });
            }

            $message->addComponent($chooseAction);
        }

        $interaction->updateMessage($message);
    }

    private function validateTravelerToDrop(Discord $discord, Interaction $interaction, Traveler $traveler, User $travelerUser): void
    {
        $embed = new Embed($discord);
        $embed->setTitle($this->translator->trans('discord.drop_traveler_from_transport.confirmation', ['traveler_display_name' => $travelerUser->displayname]));

        $validation = ActionRow::new()
            ->addComponent(Button::new(Button::STYLE_DANGER)->setLabel($this->translator->trans('discord.drop_traveler_from_transport.confirm_button'))->setEmoji('🗑️')->setListener(function (Interaction $interaction) use ($traveler, $discord): void {
                $travelerUser = $traveler->user;
                $transport = $traveler->transport;
                $this->entityManager->remove($traveler);
                $this->entityManager->flush();

                $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.drop_traveler_from_transport.confirm_label'))->setComponents([])->setEmbeds([]));
                $discord->users->fetch((string) $travelerUser->userId)->then(function (User $user) use ($transport) {
                    $direction = $this->translator->trans(Direction::EVENT === $transport->direction ? 'enum.event_with_postal_code' : 'enum.home_with_postal_code', ['postal_code' => $transport->postalCode]);
                    $user->sendMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.drop_traveler_from_transport.dropped_traveler_dm', ['direction' => $direction, 'date' => $transport->startAt->format('H\hi \o\n j F Y'), 'event_channel' => $transport->event->channelId])));
                });
            }, $discord))
            ->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.drop_traveler_from_transport.cancel_button'))->setEmoji('❌')->setListener(function (Interaction $interaction): void {
                $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.drop_traveler_from_transport.cancel_label'))->setComponents([])->setEmbeds([]));
            }, $discord));

        $interaction->updateMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation));
    }
}
