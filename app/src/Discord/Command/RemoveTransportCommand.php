<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\FindUserTransportForEvent;
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
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User as DiscordUser;
use Doctrine\ORM\EntityManagerInterface;

final readonly class RemoveTransportCommand implements CommandInterface
{
    private const COMMAND_NAME = 'remove_transport';

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
            ->setDescription('Remove the transport you created for the event');
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

            if (null === $event) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: No event found for current channel'), true);

                return;
            }

            $transport = ($this->findUserTransportForEvent)($event, $user);
            if (null === $transport) {
                $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: You have no transport created.'), true);

                return;
            }

            $embed = new Embed($discord);
            $embed->setTitle(':wastebasket: Are you sure you want to delete your transport ?');

            $validation = ActionRow::new()
                ->addComponent(Button::new(Button::STYLE_DANGER)->setLabel('Delete')->setListener(function (Interaction $interaction) use ($transport): void {
                    $transportId = $transport->id;
                    $this->entityManager->remove($transport);
                    $this->entityManager->flush();

                    $interaction->respondWithMessage(MessageBuilder::new()->setContent(sprintf(':wastebasket: Transport #%d was removed.', $transportId)), true);
                }, $discord))
                ->addComponent(Button::new(Button::STYLE_SECONDARY)->setLabel('Cancel')->setListener(function (Interaction $interaction): void {
                    $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: Ignoring removal request.'), true);
                }, $discord));

            $interaction->respondWithMessage(MessageBuilder::new()->addEmbed($embed)->addComponent($validation), true);
        });
    }
}
