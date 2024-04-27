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
use Doctrine\ORM\EntityManagerInterface;

final readonly class LeaveTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'leave_transport';

    public function __construct(
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
            ->setDescription('Leave a transport as a passenger');
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) use ($discord) {
            if ($interaction->user?->bot ?? false) {
                return; // ignore bots
            }

            if (false === ($event = $this->getEventForInteraction($interaction))) {
                return;
            }
            $user = $this->getUserForInteraction($interaction);

            $travelers = ($this->getTravelerListForUserAndEvent)($user, $event);
            if (0 === \count($travelers)) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: You have did not joined any transport for current channel\'s event.'), true);

                return;
            }

            if (1 === \count($travelers)) {
                $this->validateRemoval($discord, $interaction, $travelers[0]);
            } else {
                $embed = new Embed($discord);
                $embed->setTitle(':wastebasket: Which travel you wanna leave ?');

                $chooseAction = ActionRow::new();
                foreach ($travelers as $traveler) {
                    $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel(sprintf('[%s] %s', Direction::EVENT === $traveler->transport->direction ? 'To the event' : 'To my place', $traveler->transport->startAt->format(\DateTimeInterface::ATOM)))->setEmoji('🚗')->setListener(function (Interaction $interaction) use ($discord, $traveler): void {
                        $this->validateRemoval($discord, $interaction, $traveler);
                    }, $discord));
                }

                $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($chooseAction), true);
            }
        });
    }

    private function validateRemoval(Discord $discord, Interaction $interaction, Traveler $traveler): void
    {
        $embed = new Embed($discord);
        $embed->setTitle(':wastebasket: Are you sure you want to leave this travel ?');

        $validation = ActionRow::new()
            ->addComponent(Button::new(Button::STYLE_DANGER)->setLabel('Leave')->setEmoji('🗑️')->setListener(function (Interaction $interaction) use ($traveler): void {
                $this->entityManager->remove($traveler);
                $this->entityManager->flush();

                $interaction->respondWithMessage(MessageBuilder::new()->setContent('🗑️ You left the travel !'), true);
            }, $discord))
            ->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel('Cancel')->setEmoji('❌')->setListener(function (Interaction $interaction): void {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent('❌ Ignoring removal request.'), true);
            }, $discord));

        $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation), true);
    }
}
