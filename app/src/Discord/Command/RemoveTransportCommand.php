<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\FindUserTransportForEvent;
use Afup\Hermes\Repository\User\FindOrCreateUser;
use Discord\Builders\CommandBuilder;
use Discord\Builders\Components\Option as SelectOption;
use Discord\Builders\Components\StringSelect;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
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

            $action = StringSelect::new()
                ->addOption(new SelectOption('Yes', 'yes'))
                ->addOption(new SelectOption('No', 'no'))
                ->setListener(function (Interaction $interaction) use ($transport): void {
                    /** @var 'yes'|'no' $answer */
                    [$answer] = $interaction->data?->values ?? ['yes'];

                    if ('no' === $answer) {
                        $interaction->respondWithMessage(MessageBuilder::new()->setContent(':no_entry: Ignoring removal request.'), true);

                        return;
                    }

                    $transportId = $transport->id;
                    $this->entityManager->remove($transport);
                    $this->entityManager->flush();

                    $interaction->respondWithMessage(MessageBuilder::new()->setContent(sprintf(':wastebasket: Transport #%d was removed.', $transportId)), true);
                }, $discord);

            $interaction->respondWithMessage(MessageBuilder::new()->setContent(':wastebasket: Are you sure you want to delete your transport ?')->addComponent($action), true);
        });
    }
}
