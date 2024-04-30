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
                    ->setName('seats')
                    ->setDescription($this->translator->trans('discord.create_transport.option.seats'))
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
                    ->setName('when')
                    ->setDescription($this->translator->trans('discord.create_transport.option.when'))
                    ->setType(CommandOption::STRING)
                    ->setMinLength(19)
                    ->setMaxLength(19)
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
            $seats = $this->getOptionForInteraction($interaction, 'seats');
            /** @var string $postalCode */
            $postalCode = $this->getOptionForInteraction($interaction, 'postal_code');
            /** @var string $whenString */
            $whenString = $this->getOptionForInteraction($interaction, 'when');
            $when = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $whenString);

            if (false === $when) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.error.invalid_date')), true);

                return;
            }

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

    private function createTransport(Interaction $interaction, Event $event, User $user, int $seats, string $postalCode, \DateTimeInterface $when, Direction $direction): void
    {
        if (!($this->userCanCreateTransport)($event, $user, $direction)) {
            // possible use-cases:
            // - AFUP Day, Nantes > Lyon (one ride to go to the event, one to get back)
            // - ForumPHP, Nantes > Disneyland (one ride to go to the event, one to get back)
            // - ForumPHP, Paris > Disneyland (one ride each day to go to the event, one ride each day to get back)
            $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.error.same_configuration')), true);

            return;
        }

        $transport = new Transport($event, $seats, $postalCode, $direction, $when);
        $traveler = new Traveler($transport, $user, TravelerType::DRIVER);

        $this->entityManager->persist($transport);
        $this->entityManager->persist($traveler);
        $this->entityManager->flush();

        $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.create_transport.created', ['transport_id' => $transport->shortId])), true);
    }
}
