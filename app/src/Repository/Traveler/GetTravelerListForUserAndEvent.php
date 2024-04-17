<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Traveler;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Traveler;
use Afup\Hermes\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

final readonly class GetTravelerListForUserAndEvent
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<Traveler>
     */
    public function __invoke(User $user, Event $event): array
    {
        $sql = <<<SQL
SELECT tp.id
FROM transport tp
INNER JOIN traveler tv ON tp.id = tv.transport_id
WHERE tv.user_id = :userId
AND tp.event_id = :eventId
SQL;

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('eventId', $event->id);
        $statement->bindValue('userId', $user->id);
        $result = $statement->executeQuery();
        $transportIds = $result->fetchAllAssociative();
        $transportIds = array_map(fn (array $result) => $result['id'], $transportIds);

        $travelerRepository = $this->entityManager->getRepository(Traveler::class);
        $travelers = $travelerRepository->findBy(['transport' => $transportIds, 'user' => $user->id]);

        return $travelers;
    }
}
