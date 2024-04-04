<?php

declare(strict_types=1);

namespace Afup\Hermes\Entity;

use Afup\Hermes\Enum\Traveler as TravelerType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Traveler
{
    use CreatedAtTrait;

    public function __construct(
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: Transport::class, inversedBy: 'travelers')]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Transport $transport,

        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false)]
        public readonly User $user,

        #[ORM\Column(type: Types::STRING, enumType: TravelerType::class)]
        public readonly TravelerType $type,
    ) {
    }
}
