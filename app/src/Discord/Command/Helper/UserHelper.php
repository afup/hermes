<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command\Helper;

use Afup\Hermes\Entity\User;
use Afup\Hermes\Repository\User\FindOrCreateUserDebug;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Discord\Parts\User\User as DiscordUser;

trait UserHelper
{
    public function getUserForInteraction(Interaction $interaction): User
    {
        /** @var DiscordUser $discordUser */
        $discordUser = $interaction->user;
        $userId = (int) $discordUser->id;

        return ($this->findOrCreateUser)($userId);
    }
}
