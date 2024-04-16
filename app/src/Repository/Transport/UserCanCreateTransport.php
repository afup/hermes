<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\User;
use Afup\Hermes\Enum\Direction;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserCanCreateTransport
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Event $event, User $user, Direction $direction): bool
    {
        $sql = <<<SQL
SELECT tp.id
FROM transport tp
INNER JOIN traveler tv ON tv.transport_id = tp.id
WHERE tp.event_id = :eventId
AND DATE(tp.start_at) = DATE(NOW())
AND tv.user_id = :userId
AND tv.type = 'driver'
AND tp.direction = :direction
SQL;

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('eventId', $event->id);
        $statement->bindValue('userId', $user->id);
        $statement->bindValue('direction', $direction->value);
        $result = $statement->executeQuery();
        /** @var false|int $transportId */
        $transportId = $result->fetchOne();

        if (false === $transportId) {
            return true;
        }

        return false;
    }
}
