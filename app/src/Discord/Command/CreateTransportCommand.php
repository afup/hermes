<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\OptionHelper;
use Afup\Hermes\Discord\Command\Helper\UserHelper;
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
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Embed\Embed;
use Discord\Parts\Interactions\Command\Option as CommandOption;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class CreateTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'create_transport';

    public function __construct(
        private TranslatorInterface $translator,
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
            ->setDescription($this->translator->trans('discord.create_transport.description'))
            ->addOption(
                (new CommandOption($discord))
                    ->setName('passenger_seats')
                    ->setDescription($this->translator->trans('discord.create_transport.option.passenger_seats'))
                    ->setType(CommandOption::INTEGER)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('postal_code')
                    ->setDescription($this->translator->trans('discord.create_transport.option.postal_code'))
                    ->setType(CommandOption::STRING)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('when_date')
                    ->setDescription($this->translator->trans('discord.create_transport.option.when_date'))
                    ->setType(CommandOption::STRING)
                    ->setMinLength(10)
                    ->setMaxLength(10)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('when_time')
                    ->setDescription($this->translator->trans('discord.create_transport.option.when_time'))
                    ->setType(CommandOption::STRING)
                    ->setMinLength(5)
                    ->setMaxLength(5)
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

            /** @var int $seats */
            $seats = $this->getOptionForInteraction($interaction, 'passenger_seats');
            /** @var string $postalCode */
            $postalCode = $this->getOptionForInteraction($interaction, 'postal_code');
            /** @var string $whenDateString */
            $whenDateString = $this->getOptionForInteraction($interaction, 'when_date');
            $whenDate = \DateTimeImmutable::createFromFormat('Y-m-d', $whenDateString);
            if (false === $whenDate) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.error.invalid_date')), true);

                return;
            }

            /** @var string $whenTimeString */
            $whenTimeString = $this->getOptionForInteraction($interaction, 'when_time');
            $whenTime = \DateTimeImmutable::createFromFormat('H:i', $whenTimeString);
            if (false === $whenTime) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.error.invalid_date')), true);

                return;
            }

            $when = $whenDate->setTime((int) $whenTime->format('H'), (int) $whenTime->format('i'));

            $embed = new Embed($discord);
            $embed->setTitle($this->translator->trans('discord.create_transport.ask_direction'));

            $validation = ActionRow::new()
                ->addComponent(Button::new(Button::STYLE_PRIMARY)->setLabel($this->translator->trans('enum.event'))->setEmoji('🎤')->setListener(function (Interaction $interaction) use ($event, $user, $seats, $postalCode, $when): void {
                    $this->createTransport($interaction, $event, $user, $seats, $postalCode, $when, Direction::EVENT);
                }, $discord))
                ->addComponent(Button::new(Button::STYLE_PRIMARY)->setLabel($this->translator->trans('enum.home'))->setEmoji('🏠')->setListener(function (Interaction $interaction) use ($event, $user, $seats, $postalCode, $when): void {
                    $this->createTransport($interaction, $event, $user, $seats, $postalCode, $when, Direction::HOME);
                }, $discord));

            $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation), true);
        });
    }

    private function createTransport(Interaction $interaction, Event $event, User $user, int $seats, string $postalCode, \DateTimeImmutable $when, Direction $direction): void
    {
        if (!$this->checkTransportDateIsValid($when, $event, $direction)) {
            $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.error.invalid_date')), true);

            return;
        }

        if (!($this->userCanCreateTransport)($event, $user, $direction, $when)) {
            $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.error.same_configuration'))->setComponents([])->setEmbeds([]));

            return;
        }

        $transport = new Transport($event, $seats, $postalCode, $direction, $when);
        $traveler = new Traveler($transport, $user, TravelerType::DRIVER);

        $this->entityManager->persist($transport);
        $this->entityManager->persist($traveler);
        $this->entityManager->flush();

        $interaction->updateMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.created', ['transport_id' => $transport->shortId]))->setComponents([])->setEmbeds([]));
    }

    private function checkTransportDateIsValid(\DateTimeImmutable $when, Event $event, Direction $direction): bool
    {
        if (Direction::EVENT === $direction) {
            $earliestTransport = $event->startAt->sub(new \DateInterval('P2D'));

            // début-2 <= $when <= fin
            return $earliestTransport <= $when && $when <= $event->finishAt;
        } else {
            $latestTransport = $event->finishAt->add(new \DateInterval('P2D'));

            // début <= $when <= fin+2
            return $event->startAt <= $when && $when <= $latestTransport;
        }
    }
}
