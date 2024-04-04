<?php

declare(strict_types=1);

namespace Afup\Hermes\Entity;

use Afup\Hermes\Enum\Direction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Transport
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column(type: Types::INTEGER)]
    public int $id;

    /** @var Collection<int, Traveler> */
    #[ORM\OneToMany(targetEntity: Traveler::class, mappedBy: 'transport', orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: false)]
    public Collection $travelers;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Event::class)]
        #[ORM\JoinColumn(nullable: false)]
        public readonly Event $event,

        #[ORM\Column(type: Types::INTEGER)]
        public readonly int $seats,

        #[ORM\Column(type: Types::STRING)]
        public readonly string $postalCode,

        #[ORM\Column(type: Types::STRING, enumType: Direction::class)]
        public readonly Direction $direction,

        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        public readonly \DateTimeInterface $startAt,
    ) {
        $this->travelers = new ArrayCollection();
    }
}
