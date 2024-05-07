<?php

declare(strict_types=1);

namespace Afup\Hermes\Tests\Repository\Transport;

use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Enum\Traveler;
use Afup\Hermes\Factory\EventFactory;
use Afup\Hermes\Factory\TransportFactory;
use Afup\Hermes\Factory\TravelerFactory;
use Afup\Hermes\Factory\UserFactory;
use Afup\Hermes\Repository\Transport\UserCanCreateTransport;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserCanCreateTransportTest extends KernelTestCase
{
    use ResetDatabase;
    use Factories;

    // possible use-cases:
    // - AFUP Day, Nantes > Lyon (one ride to go to the event, one to get back)
    // - ForumPHP, Nantes > Disneyland (one ride to go to the event, one to get back)
    // - ForumPHP, Paris > Disneyland (one ride each day to go to the event, one ride each day to get back)

    public function testEventWithinSameDayOneTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinSameDay($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();

        /** @var UserCanCreateTransport $userCanCreateTransport */
        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::HOME, $eventDate->sub(new \DateInterval('P1D'))));
    }

    public function testEventWithinSameDayTwoTransportTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinSameDay($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->add(new \DateInterval('P1D')))->withDirection(Direction::HOME)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();

        /** @var UserCanCreateTransport $userCanCreateTransport */
        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
        $this->assertFalse(($userCanCreateTransport)($event->object(), $user->object(), Direction::HOME, $eventDate->add(new \DateInterval('P1D'))));
    }

    public function testTwoDaysEventWithOneTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinTwoDays($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();

        /** @var UserCanCreateTransport $userCanCreateTransport */
        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate));
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::HOME, $eventDate->add(new \DateInterval('P1D'))));
    }

    public function testTwoDaysEventWithTwoTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinTwoDays($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->add(new \DateInterval('P1D')))->withDirection(Direction::HOME)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();

        /** @var UserCanCreateTransport $userCanCreateTransport */
        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
        $this->assertFalse(($userCanCreateTransport)($event->object(), $user->object(), Direction::HOME, $eventDate->add(new \DateInterval('P1D'))));
    }

    public function testTwoDaysEventWithThreeTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinTwoDays($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->sub(new \DateInterval('P1D')))->withDirection(Direction::EVENT)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->add(new \DateInterval('P1D')))->withDirection(Direction::HOME)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();

        /** @var UserCanCreateTransport $userCanCreateTransport */
        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate));
        $this->assertTrue(($userCanCreateTransport)($event->object(), $user->object(), Direction::HOME, $eventDate));
        $this->assertFalse(($userCanCreateTransport)($event->object(), $user->object(), Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
        $this->assertFalse(($userCanCreateTransport)($event->object(), $user->object(), Direction::HOME, $eventDate->add(new \DateInterval('P1D'))));
    }
}
