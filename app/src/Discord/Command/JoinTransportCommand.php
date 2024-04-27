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
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Option as CommandOption;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class JoinTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'join_transport';

    public function __construct(
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private GetTransportForEvent $getTransportForEvent,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription('Join a transport as a passenger')
            ->addOption(
                (new CommandOption($discord))
                    ->setName('transport')
                    ->setDescription('ID of the transport you wanna join (taken from /search command)')
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
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':interrobang: Could not find a Transport for current channel Event.'), true);

                return;
            }

            $traveler = new Traveler($transport, $user, TravelerType::PASSENGER);
            $this->entityManager->persist($traveler);
            $this->entityManager->flush();

            $transportDriver = $transport->getDriver();
            $interaction->respondWithMessage(MessageBuilder::new()->setContent(sprintf(':bust_in_silhouette: You are now riding in Transport `%s`.', $transport->shortId)), true);
            $interaction->user->sendMessage(MessageBuilder::new()->setContent(sprintf('Thanks for sharing a ride with <@%d>, if you want more details about the transport please send DM to the transport creator: <@%d>', $transportDriver->userId, $transportDriver->userId)));
            $discord->users->fetch((string) $transportDriver->userId)->then(function (User $user) use ($transport, $interaction) {
                $direction = sprintf(Direction::EVENT === $transport->direction ? 'from %s to the event' : 'from the event to %s', $transport->postalCode);
                $user->sendMessage(MessageBuilder::new()->setContent(sprintf('A new co-traveler joined your transport %s (%s), you can send him a message: <@%s>', $direction, $transport->startAt->format(\DateTimeInterface::ATOM), $interaction->user->id)));
            });
        });
    }
}
