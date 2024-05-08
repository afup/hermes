<?php

declare(strict_types=1);

namespace Afup\Hermes\Tests\Repository\Transport;

use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Enum\Traveler;
use Afup\Hermes\Factory\EventFactory;
use Afup\Hermes\Factory\TransportFactory;
use Afup\Hermes\Factory\TravelerFactory;
use Afup\Hermes\Factory\UserFactory;
use Afup\Hermes\Repository\Transport\UserCanJoinTransport;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserCanJoinTransportTest extends KernelTestCase
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
        $transportToEvent = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();
        $transportToHome = TransportFactory::new()->withDirection(Direction::HOME)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();

        /** @var UserCanJoinTransport $UserCanJoinTransport */
        $UserCanJoinTransport = self::getContainer()->get(UserCanJoinTransport::class);
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEvent->object()));
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEvent->object()));
    }

    public function testEventWithinSameDayTwoTransportTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinSameDay($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->add(new \DateInterval('P1D')))->withDirection(Direction::HOME)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();

        $transportToEvent = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();
        $transportToHome = TransportFactory::new()->withDirection(Direction::HOME)->withStartAt($eventDate->add(new \DateInterval('P1D')))->create();

        /** @var UserCanJoinTransport $UserCanJoinTransport */
        $UserCanJoinTransport = self::getContainer()->get(UserCanJoinTransport::class);
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEvent->object()));
        $this->assertFalse(($UserCanJoinTransport)($event->object(), $user->object(), $transportToHome->object()));
    }

    public function testTwoDaysEventWithOneTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinTwoDays($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();

        $transportToEventBefore = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();
        $transportToEventSameDate = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate)->create();
        $transportToHome = TransportFactory::new()->withDirection(Direction::HOME)->withStartAt($eventDate->add(new \DateInterval('P1D')))->create();

        /** @var UserCanJoinTransport $UserCanJoinTransport */
        $UserCanJoinTransport = self::getContainer()->get(UserCanJoinTransport::class);
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEventBefore->object()));
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEventSameDate->object()));
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToHome->object()));
    }

    public function testTwoDaysEventWithTwoTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinTwoDays($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->add(new \DateInterval('P1D')))->withDirection(Direction::HOME)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();

        $transportToEvent = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();
        $transportToHome = TransportFactory::new()->withDirection(Direction::HOME)->withStartAt($eventDate->add(new \DateInterval('P1D')))->create();

        /** @var UserCanJoinTransport $UserCanJoinTransport */
        $UserCanJoinTransport = self::getContainer()->get(UserCanJoinTransport::class);
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEvent->object()));
        $this->assertFalse(($UserCanJoinTransport)($event->object(), $user->object(), $transportToHome->object()));
    }

    public function testTwoDaysEventWithThreeTransport(): void
    {
        $user = UserFactory::createOne();
        $event = EventFactory::new()->withinTwoDays($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->sub(new \DateInterval('P1D')))->withDirection(Direction::EVENT)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();
        $transport = TransportFactory::new()->withEvent($event->object())->withStartAt($eventDate->add(new \DateInterval('P1D')))->withDirection(Direction::HOME)->create();
        TravelerFactory::new()->withTransport($transport->object())->withUser($user->object())->withType(Traveler::DRIVER)->create();

        $transportToEvent = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();
        $transportToEventSameDate = TransportFactory::new()->withDirection(Direction::EVENT)->withStartAt($eventDate)->create();
        $transportToHome = TransportFactory::new()->withDirection(Direction::HOME)->withStartAt($eventDate->add(new \DateInterval('P1D')))->create();
        $transportToHomeSameDate = TransportFactory::new()->withDirection(Direction::HOME)->withStartAt($eventDate)->create();

        /** @var UserCanJoinTransport $UserCanJoinTransport */
        $UserCanJoinTransport = self::getContainer()->get(UserCanJoinTransport::class);
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEventSameDate->object()));
        $this->assertTrue(($UserCanJoinTransport)($event->object(), $user->object(), $transportToHomeSameDate->object()));
        $this->assertFalse(($UserCanJoinTransport)($event->object(), $user->object(), $transportToEvent->object()));
        $this->assertFalse(($UserCanJoinTransport)($event->object(), $user->object(), $transportToHome->object()));
    }
}
