<?php

declare(strict_types=1);

namespace Afup\Hermes\Factory;

use Afup\Hermes\Entity\User;
use Doctrine\ORM\EntityRepository;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method        User|Proxy                       create(array|callable $attributes = [])
 * @method static User|Proxy                       createOne(array $attributes = [])
 * @method static User|Proxy                       find(object|array|mixed $criteria)
 * @method static User|Proxy                       findOrCreate(array $attributes)
 * @method static User|Proxy                       first(string $sortedField = 'id')
 * @method static User|Proxy                       last(string $sortedField = 'id')
 * @method static User|Proxy                       random(array $attributes = [])
 * @method static User|Proxy                       randomOrCreate(array $attributes = [])
 * @method static EntityRepository|RepositoryProxy repository()
 * @method static User[]|Proxy[]                   all()
 * @method static User[]|Proxy[]                   createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy[]                   createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy[]                   findBy(array $attributes)
 * @method static User[]|Proxy[]                   randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy[]                   randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'userId' => self::faker()->randomNumber(9),
            'createdAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
        ];
    }

    public static function getClass(): string
    {
        return User::class;
    }
}
