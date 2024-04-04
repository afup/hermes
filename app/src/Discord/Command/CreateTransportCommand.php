<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Enum\Traveler as TravelerType;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\FindUserTransportForEvent;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Option as SelectOption;
use Discord\Builders\Components\SelectMenu;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\Components\TextInput;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use Discord\Parts\Interactions\Command\Option as CommandOption;

final readonly class CreateTransportCommand implements CommandInterface
{
    private const COMMAND_NAME = 'create_transport';

    public function __construct(
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private FindUserTransportForEvent $findUserTransportForEvent,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription('Create a new transport for the event')
            ->addOption(
                (new CommandOption($discord))
                    ->setName('seats')
                    ->setDescription('Number of seats available for other travelers')
                    ->setType(CommandOption::INTEGER)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('postal_code')
                    ->setDescription('Postal code you\'re coming from or you\'re going to')
                    ->setType(CommandOption::STRING)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('when')
                    ->setDescription('When you are starting your trip (format: YYYY-MM-DD HH:MM:SS)')
                    ->setType(CommandOption::STRING)
                    ->setMinLength(19)
                    ->setMaxLength(19)
                    ->setRequired(true)
            );
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) use ($discord) {
            $userId = (int) $interaction->user->id;
            $user = ($this->findOrCreateUser)($userId);

            $channelId = (int) $interaction->channel_id;
            $event = ($this->findEventByChannel)($channelId);

            $transport = ($this->findUserTransportForEvent)($event, $user);
            if ($transport instanceof Transport) {
                // @fixme we should allow one transport per user per event per day
                // possible use-cases:
                // - AFUP Day, Nantes > Lyon (one ride to go to the event, one to get back)
                // - ForumPHP, Nantes > Disneyland (one ride to go to the event, one to get back)
                // - ForumPHP, Paris > Disneyland (one ride each day to go to the event, one ride each day to get back)
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: You already have created a transport, you can\'t have more than one transport.'), true);
                return;
            }

            /** @var int $seats */
            $seats = $interaction->data->options['seats']->value;
            /** @var string $postalCode */
            $postalCode = $interaction->data->options['postal_code']->value;
            /** @var string $whenString */
            $whenString = $interaction->data->options['when']->value;
            $when = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $whenString);

            if (null === $event) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: No event found for current channel'), true);
                return;
            }

            $action = StringSelect::new()
                ->addOption(new SelectOption('To the event', 'event'))
                ->addOption(new SelectOption('To my place', 'home'))
                ->setListener(function (Interaction $interaction) use ($event, $user, $seats, $postalCode, $when): void {
                    /** @var string $directionString */
                    [$directionString] = $interaction->data->values;

                    $transport = new Transport($event, $seats, $postalCode, Direction::tryFrom($directionString), $when);
                    $traveler = new Traveler($transport, $user, TravelerType::DRIVER);

                    $this->entityManager->persist($transport);
                    $this->entityManager->persist($traveler);
                    $this->entityManager->flush();

                    $interaction->respondWithMessage(MessageBuilder::new()->setContent(sprintf(':white_check_mark: Transport #%d created.', $transport->id)), true);
                }, $discord);

            $interaction->respondWithMessage(MessageBuilder::new()->setContent(':blue_car: Are you going to the event or coming back to your place ?')->addComponent($action), true);
        });
    }
}
