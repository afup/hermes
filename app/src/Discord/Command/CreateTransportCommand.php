<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Entity\User;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Enum\Traveler as TravelerType;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\UserCanCreateTransport;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\ActionRow;
use Discord\Builders\Components\Button;
use Discord\Builders\Components\Option as SelectOption;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Guild\Emoji;
use Discord\Parts\Interactions\Command\Option as CommandOption;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\Interactions\Request\Option;
use Discord\Parts\User\User as DiscordUser;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CreateTransportCommand implements CommandInterface
{
    private const COMMAND_NAME = 'create_transport';

    public function __construct(
        private FindOrCreateUser $findOrCreateUser,
        private FindEventByChannel $findEventByChannel,
        private UserCanCreateTransport $userCanCreateTransport,
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
            /** @var DiscordUser $discordUser */
            $discordUser = $interaction->user;
            $userId = (int) $discordUser->id;
            $user = ($this->findOrCreateUser)($userId);

            $channelId = (int) $interaction->channel_id;
            $event = ($this->findEventByChannel)($channelId);

            /** @var Option[] $interactionOptions */
            $interactionOptions = $interaction->data?->options;
            /** @var int $seats */
            $seats = $interactionOptions['seats']->value;
            /** @var string $postalCode */
            $postalCode = $interactionOptions['postal_code']->value;
            /** @var string $whenString */
            $whenString = $interactionOptions['when']->value;
            $when = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $whenString);
            if (false === $when) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':clock1: Date-time passed has invalid format, please use following format: YYYY-MM-DD HH:MM:SS'), true);

                return;
            }

            if (null === $event) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: No event found for current channel'), true);

                return;
            }

            $embed = new Embed($discord);
            $embed->setTitle(':blue_car: Are you going to the event or coming back to your place ?');

            $validation = ActionRow::new()
                ->addComponent(Button::new(Button::STYLE_PRIMARY)->setLabel('To the event')->setEmoji('🎤')->setListener(function (Interaction $interaction) use ($event, $user, $seats, $postalCode, $when): void {
                    $this->createTransport($interaction, $event, $user, $seats, $postalCode, $when, Direction::EVENT);
                }, $discord))
                ->addComponent(Button::new(Button::STYLE_PRIMARY)->setLabel('To my place')->setEmoji('🏠')->setListener(function (Interaction $interaction) use ($event, $user, $seats, $postalCode, $when): void {
                    $this->createTransport($interaction, $event, $user, $seats, $postalCode, $when, Direction::HOME);
                }, $discord));

            $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation), true);
        });
    }

    private function createTransport(Interaction $interaction, Event $event, User $user, int $seats, string $postalCode, \DateTimeInterface $when, Direction $direction): void
    {
        if (!($this->userCanCreateTransport)($event, $user, $direction)) {
            // possible use-cases:
            // - AFUP Day, Nantes > Lyon (one ride to go to the event, one to get back)
            // - ForumPHP, Nantes > Disneyland (one ride to go to the event, one to get back)
            // - ForumPHP, Paris > Disneyland (one ride each day to go to the event, one ride each day to get back)
            $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: You already have created a transport with the same configuration, you can\'t have more than one transport per day and per direction.'), true);

            return;
        }

        $transport = new Transport($event, $seats, $postalCode, $direction, $when);
        $traveler = new Traveler($transport, $user, TravelerType::DRIVER);

        $this->entityManager->persist($transport);
        $this->entityManager->persist($traveler);
        $this->entityManager->flush();

        $interaction->respondWithMessage(MessageBuilder::new()->setContent(sprintf(':white_check_mark: Transport #%d created.', $transport->id)), true);
    }
}
