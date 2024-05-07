<?php

declare(strict_types=1);

namespace Afup\Hermes\Factory;

use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Entity\User;
use Afup\Hermes\Enum\Traveler as TravelerType;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Traveler>
 *
 * @method        Traveler|Proxy                   create(array|callable $attributes = [])
 * @method static Traveler|Proxy                   createOne(array $attributes = [])
 * @method static Traveler|Proxy                   find(object|array|mixed $criteria)
 * @method static Traveler|Proxy                   findOrCreate(array $attributes)
 * @method static Traveler|Proxy                   first(string $sortedField = 'id')
 * @method static Traveler|Proxy                   last(string $sortedField = 'id')
 * @method static Traveler|Proxy                   random(array $attributes = [])
 * @method static Traveler|Proxy                   randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Traveler[]|Proxy[]               all()
 * @method static Traveler[]|Proxy[]               createMany(int $number, array|callable $attributes = [])
 * @method static Traveler[]|Proxy[]               createSequence(iterable|callable $sequence)
 * @method static Traveler[]|Proxy[]               findBy(array $attributes)
 * @method static Traveler[]|Proxy[]               randomRange(int $min, int $max, array $attributes = [])
 * @method static Traveler[]|Proxy[]               randomSet(int $number, array $attributes = [])
 */
final class TravelerFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'transport' => TransportFactory::new(),
            'type' => self::faker()->randomElement(TravelerType::cases()),
            'user' => UserFactory::new(),
        ];
    }

    public function withType(TravelerType $travelerType): self
    {
        return $this->addState(['type' => $travelerType]);
    }

    public function withTransport(Transport $transport): self
    {
        return $this->addState(['transport' => $transport]);
    }

    public function withUser(User $user): self
    {
        return $this->addState(['user' => $user]);
    }

    protected static function getClass(): string
    {
        return Traveler::class;
    }
}
