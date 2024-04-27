<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Transport;

use Afup\Hermes\Entity\Event;
use Afup\Hermes\Entity\Transport;
use Doctrine\ORM\EntityManagerInterface;

final readonly class GetTransportForEvent
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Event $event, string $transportId): ?Transport
    {
        $transportRepository = $this->entityManager->getRepository(Transport::class);

        /** @var Transport|null $transport */
        $transport = $transportRepository->findOneBy(['shortId' => $transportId]);

        if (null === $transport) {
            return null;
        }

        if ($event->id !== $transport->event->id) {
            return null; // if not the same event, we ignore the transport
        }

        return $transport;
    }
}
