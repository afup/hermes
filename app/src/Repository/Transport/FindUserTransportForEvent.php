<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FindUserTransportForEvent
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Event $event, User $user): Transport|null
    {
        $sql = <<<SQL
SELECT tp.id
FROM transport tp
INNER JOIN traveler tv ON tv.transport_id = tp.id
WHERE tp.event_id = :eventId
AND tv.user_id = :userId
AND tv.type = 'driver'
SQL;

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('eventId', $event->id);
        $statement->bindValue('userId', $user->id);
        $result = $statement->executeQuery();
        /** @var false|int $transportId */
        $transportId = $result->fetchOne();

        if (false === $transportId) {
            return null;
        }

        /** @var Transport $transport */
        $transport = $this->entityManager->find(Transport::class, $transportId);

        return $transport;
    }
}
