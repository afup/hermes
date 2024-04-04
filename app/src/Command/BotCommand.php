<?php

declare(strict_types=1);

namespace Afup\Hermes\Command;

use Afup\Hermes\Discord\Command\CommandInterface;
use Afup\Hermes\Discord\Discord;
use Discord\Discord as Client;
use Discord\Http\Endpoint;

use function React\Async\await;

use React\Promise\PromiseInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

#[AsCommand(
    name: 'hermes:bot',
    description: 'Run Discord bot',
)]
final class BotCommand extends Command
{
    public function __construct(
        /** @var CommandInterface[] $commands */
        #[TaggedIterator(CommandInterface::class)]
        private readonly iterable $commands,
        private readonly Discord $discord,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->discord->on('init', function (Client $discord) {
            foreach ($this->commands as $command) {
                $command->callback($discord);
            }
        });

        $this->discord->run();

        return Command::SUCCESS;
    }
}
