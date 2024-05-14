<?php

declare(strict_types=1);

namespace Afup\Hermes\Entity;

use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Enum\Traveler as TravelerType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PUGX\Shortid\Shortid;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint('short_id_idx', columns: ['short_id'])]
class Transport
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue('SEQUENCE')]
    #[ORM\Column(type: Types::INTEGER)]
    public int $id;

    #[ORM\Column(type: Types::STRING)]
    public string $shortId;

    /** @var Collection<int, Traveler> */
    #[ORM\OneToMany(targetEntity: Traveler::class, mappedBy: 'transport', cascade: ['remove'], fetch: 'EAGER')]
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
        public readonly \DateTimeImmutable $startAt,
    ) {
        $this->travelers = new ArrayCollection();
        $this->shortId = Shortid::generate(5)->serialize();
    }

    public function getDriver(): User
    {
        /** @var Traveler $driver */
        $driver = $this->travelers->findFirst(function (int $_, Traveler $traveler) {
            return TravelerType::DRIVER === $traveler->type;
        });

        return $driver->user;
    }

    /**
     * @return \Generator<Traveler>
     */
    public function getPassengers(): \Generator
    {
        foreach ($this->travelers as $traveler) {
            if (TravelerType::PASSENGER === $traveler->type) {
                yield $traveler;
            }
        }
    }

    public function availableSeats(): int
    {
        return $this->seats - ($this->travelers->count() - 1);
    }
}
