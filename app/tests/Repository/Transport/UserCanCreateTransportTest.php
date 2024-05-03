<?php

declare(strict_types=1);

namespace Afup\Hermes\Tests\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Factory\EventFactory;
use Afup\Hermes\Factory\TransportFactory;
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

    //    public function testEventWithinSameDayOneTransport(): void
    //    {
    //        $user = UserFactory::createOne();
    //        $event = EventFactory::new()->withinSameDay($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
    //
    //        /** @var UserCanCreateTransport $userCanCreateTransport */
    //        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
    //        $this->assertTrue(($userCanCreateTransport)($event, $user, Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
    //    }
    //
    //    public function testEventWithinSameDayTwoTransportTransport(): void
    //    {
    //        $user = UserFactory::createOne();
    //        $event = EventFactory::new()->withinSameDay($eventDate = (new \DateTimeImmutable())->add(new \DateInterval('P1D')))->create();
    //        $transport = TransportFactory::new()->withEvent($event)->withStartAt($eventDate->sub(new \DateInterval('P1D')))->create();
    //
    //        /** @var UserCanCreateTransport $userCanCreateTransport */
    //        $userCanCreateTransport = self::getContainer()->get(UserCanCreateTransport::class);
    //        $this->assertTrue(($userCanCreateTransport)($event, $user, Direction::EVENT, $eventDate->sub(new \DateInterval('P1D'))));
    //    }
}
