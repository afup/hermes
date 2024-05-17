<?php

declare(strict_types=1);

namespace Afup\Hermes\Repository\User;

use Afup\Hermes\Entity\User;
use Discord\Builders\MessageBuilder;
use Discord\Parts\Interactions\Interaction;
use Doctrine\ORM\EntityManagerInterface;

final readonly class FindOrCreateUser
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(int $userId): User
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        /** @var User|null $user */
        $user = $userRepository->findOneBy(['userId' => $userId]);
        if (null === $user) {
            $user = new User($userId);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
