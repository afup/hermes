<?php

declare(strict_types=1);

namespace Afup\Hermes\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint('name_idx', ['name'])]
#[ORM\HasLifecycleCallbacks]
class Event
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column(type: Types::INTEGER)]
    public int $id;

    public function __construct(
        #[ORM\Column(type: Types::STRING, length: 255)]
        public readonly string $name,

        #[ORM\Column(type: Types::BIGINT)]
        public readonly int $channelId,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public readonly \DateTimeImmutable $startAt,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public readonly \DateTimeImmutable $finishAt,
    ) {
    }
}
