<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserCanJoinTransport
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Event $event, User $user, Transport $transport): bool
    {
        // possible use-cases:
        // - AFUP Day, Nantes > Lyon (one ride to go to the event, one to get back)
        // - ForumPHP, Nantes > Disneyland (one ride to go to the event, one to get back)
        // - ForumPHP, Paris > Disneyland (one ride each day to go to the event, one ride each day to get back)

        $sql = <<<SQL
SELECT tp.id
FROM transport tp
INNER JOIN traveler tv ON tv.transport_id = tp.id
WHERE tp.event_id = :eventId
AND DATE(tp.start_at) = :date
AND tv.user_id = :userId
AND tp.direction = :direction
SQL;

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('eventId', $event->id);
        $statement->bindValue('userId', $user->id);
        $statement->bindValue('direction', $transport->direction->value);
        $statement->bindValue('date', $transport->startAt->format('Y-m-d H:i:s'));
        $result = $statement->executeQuery();
        /** @var false|int $transportId */
        $transportId = $result->fetchOne();

        if (false === $transportId) {
            return true;
        }

        return false;
    }
}
