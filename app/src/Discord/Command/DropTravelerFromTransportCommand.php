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
use Doctrine\ORM\EntityManagerInterface;

final readonly class DropTravelerFromTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'drop_traveler_from_transport';

    public function __construct(
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
            ->setDescription('Drop a traveler from one of your transport');
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
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: You have no transport(s) created for current channel\'s event.'), true);

                return;
            }

            if (1 === \count($transports)) {
                $this->chooseTravelerToDrop($discord, $interaction, $transports[0]);
            } else {
                $embed = new Embed($discord);
                $embed->setTitle(':wastebasket: From which transport you wanna drop a traveler ?');
                $message = MessageBuilder::new()->addEmbed($embed);

                $chunkedTransports = array_chunk($transports, 5);
                foreach ($chunkedTransports as $transportRow) {
                    $chooseAction = ActionRow::new();

                    foreach ($transportRow as $transport) {
                        $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel(sprintf('[%s] %s', Direction::EVENT === $transport->direction ? 'To the event' : 'To my place', $transport->startAt->format(\DateTimeInterface::ATOM)))->setEmoji('🚗')->setListener(function (Interaction $interaction) use ($discord, $transport): void {
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
        $embed->setTitle(':wastebasket: Which traveler you wanna drop from this transport ?');
        $message = MessageBuilder::new()->addEmbed($embed);

        /** @var array<array<Traveler>> $chunkedTravelers */
        $chunkedTravelers = array_chunk(iterator_to_array($transport->getPassengers()), 5);
        foreach ($chunkedTravelers as $travelerRow) {
            $chooseAction = ActionRow::new();

            foreach ($travelerRow as $traveler) {
                $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel(sprintf('<@%s>', $traveler->user->userId))->setEmoji('👤')->setListener(function (Interaction $interaction) use ($discord, $traveler): void {
                    $this->validateTravelerToDrop($discord, $interaction, $traveler);
                }, $discord));
            }

            $message->addComponent($chooseAction);
        }

        $interaction->respondWithMessage($message, true);
    }

    private function validateTravelerToDrop(Discord $discord, Interaction $interaction, Traveler $traveler): void
    {
        $embed = new Embed($discord);
        $embed->setTitle(sprintf(':wastebasket: Are you sure you want to drop this traveler: <@%s> ?', $traveler->user->userId));

        $validation = ActionRow::new()
            ->addComponent(Button::new(Button::STYLE_DANGER)->setLabel('Drop this traveler')->setEmoji('🗑️')->setListener(function (Interaction $interaction) use ($traveler): void {
                $this->entityManager->remove($traveler);
                $this->entityManager->flush();

                $interaction->respondWithMessage(MessageBuilder::new()->setContent('🗑️ Traveler was dropped.'), true);
            }, $discord))
            ->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel('Cancel')->setEmoji('❌')->setListener(function (Interaction $interaction): void {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent('❌ Ignoring removal request.'), true);
            }, $discord));

        $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation), true);
    }
}
