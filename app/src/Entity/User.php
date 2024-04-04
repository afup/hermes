<?php

declare(strict_types=1);

namespace Afup\Hermes\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'discord_user')]
#[ORM\UniqueConstraint('user_idx', ['user_id'])]
#[ORM\HasLifecycleCallbacks]
class User
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column(type: Types::INTEGER)]
    public int $id;

    public function __construct(
        #[ORM\Column(type: Types::BIGINT)]
        public readonly int $userId,
    ) {
    }
}
