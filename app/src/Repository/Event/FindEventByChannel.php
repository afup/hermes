<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\Event;

use Afup\Hermes\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FindEventByChannel
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(int $channelId): Event|null
    {
        $eventRepository = $this->entityManager->getRepository(Event::class);

        /** @var Event|null $event */
        $event = $eventRepository->findOneBy(['channelId' => $channelId]);

        return $event;
    }
}
