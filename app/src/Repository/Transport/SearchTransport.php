<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Afup\Hermes\Enum\Direction;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SearchTransport
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @return list<Transport>
     */
    public function __invoke(Event $event, string $postalCode, Direction $direction): array
    {
        $sql = <<<SQL
SELECT tp.id
FROM transport tp
WHERE tp.event_id = :eventId
AND tp.direction = :direction

SQL;
        if (5 === \mb_strlen($postalCode)) {
            $sql .= 'AND tp.postal_code = :postalCode';
        } else {
            $postalCode = $postalCode . '%';
            $sql .= 'AND tp.postal_code LIKE :postalCode';
        }

        $connection = $this->entityManager->getConnection();
        $statement = $connection->prepare($sql);
        $statement->bindValue('eventId', $event->id);
        $statement->bindValue('direction', $direction->value);
        $statement->bindValue('postalCode', $postalCode);
        $result = $statement->executeQuery();
        $transportIds = array_map(fn (array $row) => $row['id'], $result->fetchAllAssociative());

        /** @var list<Transport> $transports */
        $transports = $this->entityManager->getRepository(Transport::class)->findBy(['id' => $transportIds]);

        return $transports;
    }
}
