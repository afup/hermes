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
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'hermes:create-event',
    description: 'Create an event',
)]
final class EventCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $nameQuestion = new Question($this->translator->trans('command.create_event.event_name'));
        $nameQuestion->setMaxAttempts(3);
        $nameQuestion->setValidator(function (mixed $answer) {
            if (!is_string($answer) || \mb_strlen($answer) <= 3) {
                throw new \RuntimeException($this->translator->trans('command.create_event.error.event_name'));
            }

            $eventRepository = $this->entityManager->getRepository(Event::class);
            /** @var Event|null $event */
            $event = $eventRepository->findOneBy(['name' => $answer]);
            if ($event instanceof Event) {
                throw new \RuntimeException($this->translator->trans('command.create_event.error.name_exists'));
            }

            return $answer;
        });
        /** @var string $eventName */
        $eventName = $io->askQuestion($nameQuestion);

        $channelQuestion = new Question($this->translator->trans('command.create_event.channel_id'));
        $channelQuestion->setMaxAttempts(3);
        $channelQuestion->setValidator(function (mixed $answer) {
            if (is_numeric($answer)) {
                $answer = (int) $answer;
            }

            if (!is_int($answer)) {
                throw new \RuntimeException($this->translator->trans('command.create_event.error.channel_id'));
            }

            return $answer;
        });
        /** @var int $eventChannelId */
        $eventChannelId = $io->askQuestion($channelQuestion);

        $startAtQuestion = new Question($this->translator->trans('command.create_event.start_date'));
        $startAtQuestion->setMaxAttempts(3);
        $startAtQuestion->setValidator(function (mixed $answer) {
            $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $answer);

            if (null === $answer || false === $dateTime) {
                throw new \RuntimeException($this->translator->trans('command.create_event.error.date_format'));
            }

            return $dateTime->setTime(10, 0);
        });
        /** @var \DateTimeImmutable $eventStartAt */
        $eventStartAt = $io->askQuestion($startAtQuestion);

        $finishAtQuestion = new Question($this->translator->trans('command.create_event.finish_date'));
        $finishAtQuestion->setMaxAttempts(3);
        $finishAtQuestion->setValidator(function (mixed $answer) use ($eventStartAt) {
            $dateTime = \DateTimeImmutable::createFromFormat('Y-m-d', $answer);

            if (null === $answer || false === $dateTime) {
                throw new \RuntimeException($this->translator->trans('command.create_event.error.date_format'));
            }

            if (1 === $eventStartAt->diff($dateTime)->invert) {
                throw new \RuntimeException($this->translator->trans('command.create_event.error.finish_date'));
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
        $io->success($this->translator->trans('command.create_event.created', ['name' => $eventName]));

        return Command::SUCCESS;
    }
}
