<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\OptionHelper;
use Afup\Hermes\Discord\Command\Helper\UserHelper;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Traveler\GetTravelerListForUserAndEvent;
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

final readonly class QuitTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'quit_transport';

    public function __construct(
        private TranslatorInterface $translator,
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private GetTravelerListForUserAndEvent $getTravelerListForUserAndEvent,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription($this->translator->trans('discord.quit_transport.description'));
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

            $travelers = ($this->getTravelerListForUserAndEvent)($user, $event);
            if (0 === \count($travelers)) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.quit_transport.error.no_transport')), true);

                return;
            }

            if (1 === \count($travelers)) {
                $this->validateRemoval($discord, $interaction, $travelers[0]);
            } else {
                $embed = new Embed($discord);
                $embed->setTitle($this->translator->trans('discord.quit_transport.travel_choice'));
                $message = MessageBuilder::new()->addEmbed($embed);

                /** @var array<array<Traveler>> $chunkedTravelers */
                $chunkedTravelers = array_chunk($travelers, 5);
                foreach ($chunkedTravelers as $travelerRow) {
                    $chooseAction = ActionRow::new();

                    foreach ($travelerRow as $traveler) {
                        $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.quit_transport.choice_button', ['direction' => Direction::EVENT === $traveler->transport->direction ? $this->translator->trans('enum.event') : $this->translator->trans('enum.home'), 'date' => $traveler->transport->startAt->format('H\hi \o\n j F Y')]))->setEmoji('🚗')->setListener(function (Interaction $interaction) use ($discord, $traveler): void {
                            $this->validateRemoval($discord, $interaction, $traveler);
                        }, $discord));
                    }

                    $message->addComponent($chooseAction);
                }

                $interaction->respondWithMessage($message, true);
            }
        });
    }

    private function validateRemoval(Discord $discord, Interaction $interaction, Traveler $traveler): void
    {
        $embed = new Embed($discord);
        $embed->setTitle($this->translator->trans('discord.quit_transport.confirmation'));

        $validation = ActionRow::new()
            ->addComponent(Button::new(Button::STYLE_DANGER)->setLabel($this->translator->trans('discord.quit_transport.confirm_button'))->setEmoji('🗑️')->setListener(function (Interaction $interaction) use ($traveler, $discord): void {
                $transport = $traveler->transport;
                $this->entityManager->remove($traveler);
                $this->entityManager->flush();

                $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.quit_transport.confirm_label'))->setComponents([])->setEmbeds([]));
                $discord->users->fetch((string) $transport->getDriver()->userId)->then(function (User $user) use ($transport) {
                    $direction = $this->translator->trans(Direction::EVENT === $transport->direction ? 'enum.event_with_postal_code' : 'enum.home_with_postal_code', ['postal_code' => $transport->postalCode]);
                    $user->sendMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.quit_transport.driver_dm', ['direction' => $direction, 'date' => $transport->startAt->format('H\hi \o\n j F Y'), 'seats_remaining' => $transport->availableSeats(), 'seats_total' => $transport->seats])));
                });
            }, $discord))
            ->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.quit_transport.cancel_button'))->setEmoji('❌')->setListener(function (Interaction $interaction): void {
                $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.quit_transport.cancel_label'))->setComponents([])->setEmbeds([]));
            }, $discord));

        $interaction->updateMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation));
    }
}
