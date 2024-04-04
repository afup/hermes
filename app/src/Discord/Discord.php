<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord;

use Discord\Discord as Client;
use Discord\WebSockets\Intents;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;

final class Discord extends Client
{
    public function __construct(
        #[Target('discordLogger')] LoggerInterface $logger,
        string $discordToken,
    ) {
        parent::__construct([
            'token' => $discordToken,
            'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
            'logger' => $logger,
        ]);
    }
}
