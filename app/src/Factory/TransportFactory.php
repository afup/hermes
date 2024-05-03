<?php

declare(strict_types=1);

namespace Afup\Hermes\Factory;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Enum\Direction;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Transport>
 *
 * @method        Transport|Proxy                  create(array|callable $attributes = [])
 * @method static Transport|Proxy                  createOne(array $attributes = [])
 * @method static Transport|Proxy                  find(object|array|mixed $criteria)
 * @method static Transport|Proxy                  findOrCreate(array $attributes)
 * @method static Transport|Proxy                  first(string $sortedField = 'id')
 * @method static Transport|Proxy                  last(string $sortedField = 'id')
 * @method static Transport|Proxy                  random(array $attributes = [])
 * @method static Transport|Proxy                  randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Transport[]|Proxy[]              all()
 * @method static Transport[]|Proxy[]              createMany(int $number, array|callable $attributes = [])
 * @method static Transport[]|Proxy[]              createSequence(iterable|callable $sequence)
 * @method static Transport[]|Proxy[]              findBy(array $attributes)
 * @method static Transport[]|Proxy[]              randomRange(int $min, int $max, array $attributes = [])
 * @method static Transport[]|Proxy[]              randomSet(int $number, array $attributes = [])
 */
final class TransportFactory extends PersistentObjectFactory
{
    protected function defaults(): array
    {
        return [
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'direction' => self::faker()->randomElement(Direction::cases()),
            'event' => EventFactory::new(),
            'postalCode' => self::faker()->text(),
            'seats' => self::faker()->randomNumber(),
            'shortId' => self::faker()->uuid(),
            'startAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    public function withEvent(Event $event): self
    {
        return $this->with(['event' => $event]);
    }

    public function withStartAt(\DateTimeImmutable $date): self
    {
        return $this->with(['startAt' => $date]);
    }

    public static function class(): string
    {
        return Transport::class;
    }
}
