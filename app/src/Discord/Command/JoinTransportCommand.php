<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\OptionHelper;
use Afup\Hermes\Discord\Command\Helper\UserHelper;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Enum\Traveler as TravelerType;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\GetTransportForEvent;
use Afup\Hermes\Repository\Transport\UserCanJoinTransport;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option as CommandOption;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class JoinTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'join_transport';

    public function __construct(
        private TranslatorInterface $translator,
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private GetTransportForEvent $getTransportForEvent,
        private UserCanJoinTransport $userCanJoinTransport,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription($this->translator->trans('discord.join_transport.description'))
            ->addOption(
                (new CommandOption($discord))
                    ->setName('transport')
                    ->setDescription($this->translator->trans('discord.join_transport.option.transport'))
                    ->setType(CommandOption::STRING)
                    ->setRequired(true)
            );
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
            /** @var string $transportId */
            $transportId = $this->getOptionForInteraction($interaction, 'transport');

            $transport = ($this->getTransportForEvent)($event, $transportId);
            if (null === $transport) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.error.no_transport')), true);

                return;
            }

            if ($user->userId === $transport->getDriver()->userId) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.error.created_transport')), true);

                return;
            }

            if (!($this->userCanJoinTransport)($event, $user, $transport)) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.error.same_configuration')), true);

                return;
            }

            if ($transport->seats === \count(iterator_to_array($transport->getPassengers()))) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.error.transport_full')), true);

                return;
            }

            $traveler = new Traveler($transport, $user, TravelerType::PASSENGER);
            $this->entityManager->persist($traveler);
            $this->entityManager->flush();
            $this->entityManager->clear();

            $transportDriver = $transport->getDriver();
            $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.validation_direct', ['transport_id' => $transport->shortId])), true);
            $interaction->user->sendMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.validation_dm', ['driver_id' => $transportDriver->userId])));
            $discord->users->fetch((string) $transportDriver->userId)->then(function (User $user) use ($transport, $interaction) {
                $direction = $this->translator->trans(Direction::EVENT === $transport->direction ? 'enum.event_with_postal_code' : 'enum.home_with_postal_code', ['postal_code' => $transport->postalCode]);
                $user->sendMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.join_transport.validation_driver', ['direction' => $direction, 'hour' => $transport->startAt->format('H\hi'), 'date' => $transport->startAt->format('j F Y'), 'traveler_id' => $interaction->user->id])));
            });
        });
    }
}
