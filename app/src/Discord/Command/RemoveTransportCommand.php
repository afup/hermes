<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\UserHelper;
use Afup\Hermes\Entity\Transport;
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
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class RemoveTransportCommand implements CommandInterface
{
    use EventHelper;
    use UserHelper;

    private const COMMAND_NAME = 'remove_transport';

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
            ->setDescription($this->translator->trans('discord.remove_transport.description'));
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
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.remove_transport.error.no_transport')), true);

                return;
            }

            if (1 === \count($transports)) {
                $this->validateRemoval($discord, $interaction, $transports[0]);
            } else {
                $embed = new Embed($discord);
                $embed->setTitle($this->translator->trans('discord.remove_transport.ask_remove'));
                $message = MessageBuilder::new()->addEmbed($embed);

                /** @var array<array<Transport>> $chunkedTransports */
                $chunkedTransports = array_chunk($transports, 5);
                foreach ($chunkedTransports as $transportRow) {
                    $chooseAction = ActionRow::new();

                    foreach ($transportRow as $transport) {
                        $chooseAction->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.remove_transport.button_label', ['direction' => Direction::EVENT === $transport->direction ? $this->translator->trans('enum.event') : $this->translator->trans('enum.home'), 'date' => $transport->startAt->format(\DateTimeInterface::ATOM)]))->setEmoji('🚗')->setListener(function (Interaction $interaction) use ($discord, $transport): void {
                            $this->validateRemoval($discord, $interaction, $transport);
                        }, $discord));
                    }

                    $message->addComponent($chooseAction);
                }

                $interaction->respondWithMessage($message, true);
            }
        });
    }

    private function validateRemoval(Discord $discord, Interaction $interaction, Transport $transport): void
    {
        $embed = new Embed($discord);
        $embed->setTitle($this->translator->trans('discord.remove_transport.validation_remove'));

        $validation = ActionRow::new()
            ->addComponent(Button::new(Button::STYLE_DANGER)->setLabel($this->translator->trans('discord.remove_transport.button_validation'))->setEmoji('🗑️')->setListener(function (Interaction $interaction) use ($transport): void {
                $transportId = $transport->shortId;
                $this->entityManager->remove($transport);
                $this->entityManager->flush();

                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.remove_transport.label_validation', ['transport_id' => $transportId])), true);
            }, $discord))
            ->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel($this->translator->trans('discord.remove_transport.button_cancel'))->setEmoji('❌')->setListener(function (Interaction $interaction): void {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent($this->translator->trans('discord.remove_transport.label_cancel')), true);
            }, $discord));

        $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation), true);
    }
}
