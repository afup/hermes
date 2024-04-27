<?php

declare(strict_types=1);

namespace Afup\Hermes\Discord\Command;

use Afup\Hermes\Discord\Command\Helper\EventHelper;
use Afup\Hermes\Discord\Command\Helper\OptionHelper;
use Afup\Hermes\Enum\Direction;
use Afup\Hermes\Repository\Event\FindEventByChannel;
use Afup\Hermes\Repository\Transport\SearchTransport;
use Discord\Builders\CommandBuilder;
use Discord\Builders\MessageBuilder;
use Discord\Discord;
use Discord\Parts\Interactions\Command\Choice;
use Discord\Parts\Interactions\Command\Option as CommandOption;
use Discord\Parts\Interactions\Interaction;

final readonly class SearchCommand implements CommandInterface
{
    use EventHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'search';

    public function __construct(
        private FindEventByChannel $findEventByChannel,
        private SearchTransport $searchTransport,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription('Search a transport for a given postal code')
            ->addOption(
                (new CommandOption($discord))
                    ->setName('postal_code')
                    ->setDescription('Postal code you\'re coming from or you\'re going to')
                    ->setType(CommandOption::STRING)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('direction')
                    ->setDescription('If you\'re going to the event or coming back from it')
                    ->setType(CommandOption::STRING)
                    ->addChoice(Choice::new($discord, 'To the event', Direction::EVENT->value))
                    ->addChoice(Choice::new($discord, 'Back to home', Direction::HOME->value))
                    ->setRequired(true)
            );
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) {
            if ($interaction->user?->bot ?? false) {
                return; // ignore bots
            }

            if (false === ($event = $this->getEventForInteraction($interaction))) {
                return;
            }

            /** @var string $postalCode */
            $postalCode = $this->getOptionForInteraction($interaction, 'postal_code');
            /** @var string $directionString */
            $directionString = $this->getOptionForInteraction($interaction, 'direction');
            /** @var Direction $direction */
            $direction = Direction::tryFrom($directionString);

            $transports = ($this->searchTransport)($event, $postalCode, $direction);

            $content = 'Transports found:' . "\n";
            foreach ($transports as $transport) {
                // @fixme issue with available seats
                $content .= sprintf('- [`%s`] %s %s leaving at %s - %d/%d seats available leaving', $transport->shortId, Direction::EVENT === $transport->direction ? 'From' : 'To', $transport->postalCode, $transport->startAt->format(\DateTimeInterface::ATOM), $transport->availableSeats(), $transport->seats) . "\n";
            }

            $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
        });
    }
}
