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
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class SearchCommand implements CommandInterface
{
    use EventHelper;
    use OptionHelper;

    private const COMMAND_NAME = 'search';

    public function __construct(
        private TranslatorInterface $translator,
        private FindEventByChannel $findEventByChannel,
        private SearchTransport $searchTransport,
    ) {
    }

    public function configure(Discord $discord): CommandBuilder
    {
        return CommandBuilder::new()
            ->setName(self::COMMAND_NAME)
            ->setDescription($this->translator->trans('discord.search.description'))
            ->addOption(
                (new CommandOption($discord))
                    ->setName('postal_code')
                    ->setDescription($this->translator->trans('discord.search.option.postal_code'))
                    ->setType(CommandOption::STRING)
                    ->setRequired(true)
            )
            ->addOption(
                (new CommandOption($discord))
                    ->setName('direction')
                    ->setDescription($this->translator->trans('discord.search.option.direction'))
                    ->setType(CommandOption::STRING)
                    ->addChoice(Choice::new($discord, $this->translator->trans('enum.event'), Direction::EVENT->value))
                    ->addChoice(Choice::new($discord, $this->translator->trans('enum.home'), Direction::HOME->value))
                    ->setRequired(true)
            );
    }

    public function callback(Discord $discord): void
    {
        $discord->listenCommand(self::COMMAND_NAME, function (Interaction $interaction) {
            if (null === $interaction->user || $interaction->user->bot) {
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
            $fullTransports = $showedTransports = 0;
            $content = $this->translator->trans('discord.search.intro') . "\n";
            foreach ($transports as $transport) {
                if ($transport->isFull()) {
                    ++$fullTransports;
                    continue;
                }

                $content .= $this->translator->trans('discord.search.row', ['transport_id' => $transport->shortId, 'direction' => Direction::EVENT === $transport->direction ? 'From' : 'To', 'postal_code' => $transport->postalCode, 'hour' => $transport->startAt->format('H\hi'), 'date' => $transport->startAt->format('j F Y'), 'seats_remaining' => $transport->availableSeats(), 'seats_total' => $transport->seats]);
                if ((string) $transport->getDriver()->userId === $interaction->user->id) {
                    $content .= $this->translator->trans('discord.search.row_driver');
                }
                $content .= "\n";
                ++$showedTransports;
            }

            if (0 === $showedTransports) { // if all transports are full or no transports are found for given postal code
                $content = $this->translator->trans('discord.search.empty') . "\n";
            }
            if ($fullTransports > 0) {
                $content .= $this->translator->trans('discord.search.full_transports', ['count' => $fullTransports]);
            }

            $interaction->respondWithMessage(MessageBuilder::new()->setContent($content), true);
        });
    }
}
