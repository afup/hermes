<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FindUserTransportsForEvent
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<Transport>
     */
    public function __invoke(Event $event, User $user): array
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
        $transportIds = $result->fetchAllAssociative();
        $transportIds = array_map(fn (array $result) => $result['id'], $transportIds);

        if (0 === \count($transportIds)) {
            return [];
        }

        /** @var list<Transport> $transports */
        $transports = $this->entityManager->getRepository(Transport::class)->findBy(['id' => $transportIds]);

        return $transports;
    }
}
