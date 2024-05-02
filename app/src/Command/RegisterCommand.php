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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        private readonly TranslatorInterface $translator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('filtered_command', InputArgument::OPTIONAL, 'Command to register');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string|null $filteredCommand */
        $filteredCommand = $input->getArgument('filtered_command');

        $this->discord->on('init', function (Client $discord) use ($io, $filteredCommand) {
            $this
                ->findAllCommands($discord)
                ->then(function (array $commands) use ($discord, $io, $filteredCommand) {
                    if (0 === \count($commands)) {
                        $io->info($this->translator->trans('command.register.error.no_slash_commands'));

                        return;
                    }

                    $io->title($this->translator->trans('command.register.to_clean', ['commands' => \count($commands)]));
                    foreach ($commands as $command) {
                        if (null !== $filteredCommand && $filteredCommand !== $command->name) {
                            continue;
                        }

                        $io->note($this->translator->trans('command.register.cleaning', ['command' => $command->name, 'id' => $command->id]));
                        await($discord->application->commands->delete($command->id));
                    }
                })
                ->then(function () use ($discord, $io, $filteredCommand) {
                    $io->title($this->translator->trans('command.register.to_register', ['commands' => \count(iterator_to_array($this->commands))]));

                    foreach ($this->commands as $command) {
                        $configuredCommand = $command->configure($discord)->toArray();
                        if (null !== $filteredCommand && $filteredCommand !== $configuredCommand['name']) {
                            continue;
                        }

                        $io->note($this->translator->trans('command.register.register', ['command' => $configuredCommand['name']]));
                        await($discord->application->commands->save($discord->application->commands->create($configuredCommand)));
                    }
                })
                ->then(function () use ($discord, $io) {
                    $io->success($this->translator->trans('command.register.success'));
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
