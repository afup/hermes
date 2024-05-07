<?php

declare(strict_types=1);

namespace Afup\Hermes\Factory;

use Afup\Hermes\Entity\Event;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<Event>
 *
 * @method        Event|Proxy                      create(array|callable $attributes = [])
 * @method static Event|Proxy                      createOne(array $attributes = [])
 * @method static Event|Proxy                      find(object|array|mixed $criteria)
 * @method static Event|Proxy                      findOrCreate(array $attributes)
 * @method static Event|Proxy                      first(string $sortedField = 'id')
 * @method static Event|Proxy                      last(string $sortedField = 'id')
 * @method static Event|Proxy                      random(array $attributes = [])
 * @method static Event|Proxy                      randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static Event[]|Proxy[]                  all()
 * @method static Event[]|Proxy[]                  createMany(int $number, array|callable $attributes = [])
 * @method static Event[]|Proxy[]                  createSequence(iterable|callable $sequence)
 * @method static Event[]|Proxy[]                  findBy(array $attributes)
 * @method static Event[]|Proxy[]                  randomRange(int $min, int $max, array $attributes = [])
 * @method static Event[]|Proxy[]                  randomSet(int $number, array $attributes = [])
 */
final class EventFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        $startAt = self::faker()->dateTimeBetween(self::faker()->dateTime(), self::faker()->dateTimeInInterval('+2 days'));

        return [
            'channelId' => self::faker()->unique()->randomNumber(9),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeInInterval('-2 days')),
            'name' => self::faker()->unique()->text(100),
            'startAt' => \DateTimeImmutable::createFromMutable($startAt),
            'finishAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeInInterval($startAt, sprintf('+ %s days', self::faker()->numberBetween(0, 2)))),
        ];
    }

    public function withinSameDay(?\DateTimeImmutable $eventDay = null): self
    {
        if (null === $eventDay) {
            $eventDay = \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween(self::faker()->dateTime(), self::faker()->dateTimeInInterval('+2 days')));
        }

        $startAt = $eventDay->setTime(9, 30);
        $finishAt = $eventDay->setTime(18, 00);

        return $this->addState(['startAt' => $startAt, 'finishAt' => $finishAt]);
    }

    public function withinTwoDays(?\DateTimeImmutable $eventDay = null): self
    {
        if (null === $eventDay) {
            $eventDay = \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween(self::faker()->dateTime(), self::faker()->dateTimeInInterval('+2 days')));
        }

        $startAt = $eventDay->setTime(9, 30);
        $finishAt = $eventDay->add(new \DateInterval('P1D'))->setTime(18, 00);

        return $this->addState(['startAt' => $startAt, 'finishAt' => $finishAt]);
    }

    public static function getClass(): string
    {
        return Event::class;
    }
}
