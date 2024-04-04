<?php

declare(strict_types=1);

namespace Afup\Hermes\Command;

use Afup\Hermes\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'hermes:create-event',
    description: 'Create an event',
)]
final class EventCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nameQuestion = new Question('What is the name of the event?');
        $nameQuestion->setMaxAttempts(3);
        $nameQuestion->setValidator(function (mixed $answer) {
            if (!is_string($answer) || \mb_strlen($answer) <= 3) {
                throw new \RuntimeException('Event name needs to be a string with at least 3 characters.');
            }

            $eventRepository = $this->entityManager->getRepository(Event::class);
            /** @var Event|null $event */
            $event = $eventRepository->findOneBy(['name' => $answer]);
            if ($event instanceof Event) {
                throw new \RuntimeException('An event with the same name already exists, please use a different name.');
            }

            return $answer;
        });
        /** @var string $eventName */
        $eventName = $io->askQuestion($nameQuestion);

        $channelQuestion = new Question('What is the channel ID where the bot will operate?');
        $channelQuestion->setMaxAttempts(3);
        $channelQuestion->setValidator(function (mixed $answer) {
            if (is_numeric($answer)) {
                $answer = (int) $answer;
            }

            if (!is_int($answer)) {
                throw new \RuntimeException('Event channel ID needs to be an integer.');
            }

            return $answer;
        });
        /** @var int $eventChannelId */
        $eventChannelId = $io->askQuestion($channelQuestion);

        $startAtQuestion = new Question('When does the event start? (format: YYYY-MM-DD)');
        $startAtQuestion->setMaxAttempts(3);
        $startAtQuestion->setValidator(function (mixed $answer) {
            $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $answer);

            if (null === $answer || false === $dateTime) {
                throw new \RuntimeException('Incorrect date-time given, please give a date-time with the following format: 2024-04-13 [YYYY-MM-DD].');
            }

            return $dateTime->setTime(10, 0);
        });
        /** @var \DateTimeImmutable $eventStartAt */
        $eventStartAt = $io->askQuestion($startAtQuestion);

        $finishAtQuestion = new Question('When does the event finish? (format: YYYY-MM-DD)');
        $finishAtQuestion->setMaxAttempts(3);
        $finishAtQuestion->setValidator(function (mixed $answer) use ($eventStartAt) {
            $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $answer);

            if (null === $answer || false === $dateTime) {
                throw new \RuntimeException('Incorrect date-time given, please give a date-time with the following format: 2024-04-13 [YYYY-MM-DD].');
            }

            if (1 === $eventStartAt->diff($dateTime)->invert) {
                throw new \RuntimeException('Incorrect date-time given, finishing date should be same or greater than starting date-time.');
            }

            return $dateTime->setTime(18, 0);
        });
        /** @var \DateTimeImmutable $eventFinishAt */
        $eventFinishAt = $io->askQuestion($finishAtQuestion);

        $event = new Event(
            $eventName,
            $eventChannelId,
            $eventStartAt,
            $eventFinishAt,
        );
        $this->entityManager->persist($event);
        $this->entityManager->flush();
        $io->success(sprintf('Created event `%s`', $eventName));

        return Command::SUCCESS;
    }
}
