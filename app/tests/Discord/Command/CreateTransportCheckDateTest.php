<?php

declare(strict_types=1);

namespace Afup\Hermes\Tests\Discord\Command;

use Afup\Hermes\Discord\Command\CreateTransportCommand;
use Afup\Hermes\Entity\Event;
use Afup\Hermes\Enum\Direction;
use PHPUnit\Framework\TestCase;

/**
 * @time-sensitive
 */
class CreateTransportCheckDateTest extends TestCase
{
    public function testTransportToEvent(): void
    {
        [$instance, $method] = $this->getInstanceAndMethod();

        // starts: J+1, finish: J+1
        $event = new Event('channel', 1, $eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')), $eventDate);

        $this->assertTrue($method->invoke($instance, new \DateTimeImmutable(), $event, Direction::EVENT));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->sub(new \DateInterval('P1D')), $event, Direction::EVENT));
        $this->assertFalse($method->invoke($instance, (new \DateTimeImmutable())->sub(new \DateInterval('P3D')), $event, Direction::EVENT));
        $this->assertFalse($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P1D')), $event, Direction::EVENT));

        // starts: J, finish: J+2
        $event = new Event('channel', 1, new \DateTimeImmutable(), (new \DateTimeImmutable())->add(new \DateInterval('P2D')));

        $this->assertTrue($method->invoke($instance, new \DateTimeImmutable(), $event, Direction::EVENT));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->sub(new \DateInterval('P1D')), $event, Direction::EVENT));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P1D')), $event, Direction::EVENT));
        $this->assertFalse($method->invoke($instance, (new \DateTimeImmutable())->sub(new \DateInterval('P3D')), $event, Direction::EVENT));
        $this->assertFalse($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P2D')), $event, Direction::EVENT));
    }

    public function testTransportToHome(): void
    {
        [$instance, $method] = $this->getInstanceAndMethod();

        // starts: J+1, finish: J+1
        $event = new Event('channel', 1, $eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')), $eventDate);

        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P1D')), $event, Direction::HOME));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P2D')), $event, Direction::HOME));
        $this->assertFalse($method->invoke($instance, new \DateTimeImmutable(), $event, Direction::HOME));

        // starts: J, finish: J+2
        $event = new Event('channel', 1, new \DateTimeImmutable(), (new \DateTimeImmutable())->add(new \DateInterval('P2D')));

        $this->assertTrue($method->invoke($instance, new \DateTimeImmutable(), $event, Direction::HOME));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P1D')), $event, Direction::HOME));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P2D')), $event, Direction::HOME));
        $this->assertTrue($method->invoke($instance, (new \DateTimeImmutable())->add(new \DateInterval('P3D')), $event, Direction::HOME));
        $this->assertFalse($method->invoke($instance, (new \DateTimeImmutable())->sub(new \DateInterval('P1D')), $event, Direction::HOME));
    }

    /**
     * @return array{0: CreateTransportCommand, 1: \ReflectionMethod}
     */
    private function getInstanceAndMethod(): array
    {
        $reflClass = new \ReflectionClass(CreateTransportCommand::class);
        $fakeInstance = $reflClass->newInstanceWithoutConstructor();
        $checkTransportDateIsValidMethod = $reflClass->getMethod('checkTransportDateIsValid');
        $checkTransportDateIsValidMethod->setAccessible(true);

        return [$fakeInstance, $checkTransportDateIsValidMethod];
    }
}
