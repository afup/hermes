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
    name: 'hermes:register',
    description: 'Register slash commands within Discord',
)]
final class RegisterCommand extends Command
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

        $this->discord->on('init', function (Client $discord) use ($io) {
            $this
                ->findAllCommands($discord)
                ->then(function (array $commands) use ($discord, $io) {
                    if (0 === \count($commands)) {
                        $io->info('No slash command to clean.');

                        return;
                    }

                    $io->title(sprintf('Found %d slash commands to clean ...', \count($commands)));
                    foreach ($commands as $command) {
                        $io->note(sprintf('Deleting slash command: /%s [%d]', $command->name, $command->id));
                        await($discord->application->commands->delete($command->id));
                    }
                })
                ->then(function () use ($discord, $io) {
                    $io->title(sprintf('Found %d slash commands to register ...', \count(iterator_to_array($this->commands))));
                    foreach ($this->commands as $command) {
                        $configuredCommand = $command->configure($discord)->toArray();
                        $io->note(sprintf('Registering slash command: /%s', $configuredCommand['name']));
                        await($discord->application->commands->save($discord->application->commands->create($configuredCommand)));
                    }
                })
                ->then(function () use ($discord, $io) {
                    $io->success('Slash commands were successfully registered ! ✅');
                    $discord->close();
                });
        });

        $this->discord->run();

        return Command::SUCCESS;
    }

    private function findAllCommands(Client $discord): PromiseInterface
    {
        $endpoint = new Endpoint(Endpoint::GLOBAL_APPLICATION_COMMANDS);
        $endpoint->bindAssoc(['application_id' => $discord->application->id]);

        return $discord->getHttpClient()->get($endpoint);
    }
}
