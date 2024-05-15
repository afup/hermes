<?php

declare(strict_types=1);

return [
    'enum' => [
        'event' => 'To the event',
        'event_with_postal_code' => 'from {postal_code} to the event',
        'home' => 'To my place',
        'home_with_postal_code' => 'from the event to {postal_code}',
    ],
    'command' => [
        'create_event' => [
            'event_name' => 'What is the name of the event?',
            'channel_id' => 'What is the channel ID where the bot will operate?',
            'start_date' => 'When does the event start? (format: YYYY-MM-DD)',
            'finish_date' => 'When does the event finish? (format: YYYY-MM-DD)',
            'created' => 'Created event `{name}`',
            'error' => [
                'event_name' => 'Event name needs to be a string with at least 3 characters.',
                'name_exists' => 'An event with the same name already exists, please use a different name.',
                'channel_id' => 'Event channel ID needs to be an integer.',
                'date_format' => 'Incorrect date-time given, please give a date-time with the following format: 2024-04-13 [YYYY-MM-DD].',
                'finish_date' => 'Incorrect date-time given, finishing date should be same or greater than starting date-time.',
            ],
        ],
        'register' => [
            'to_clean' => '{commands, plural,
                =0    {Found no slash commands to clean ...}
                =1    {Found 1 slash command to clean ...}
                other {Found # slash commands to clean ...}
            }',
            'cleaning' => 'Deleting slash command: /{command} [{id}]',
            'to_register' => '{commands, plural,
                =0    {Found no slash commands to register ...}
                =1    {Found 1 slash command to register ...}
                other {Found # slash commands to register ...}
            }',
            'register' => 'Registering slash command: /{command}',
            'success' => 'Slash commands were successfully registered ! ✅',
            'error' => [
                'no_slash_commands' => 'No slash command to clean.',
            ],
        ],
    ],
    'discord' => [
        'create_transport' => [
            'description' => 'Create a new transport for the event',
            'option' => [
                'passenger_seats' => 'Number of seats available for other travelers',
                'postal_code' => 'Postal code you’re coming from on your way to the event or you’re going back to',
                'when_date' => 'The day of your transport (format: YYYY-MM-DD)',
                'when_time' => 'The time of your transport (format: HH:MM)',
            ],
            'ask_direction' => '🚙 Are you going to the event or coming back to your place ?',
            'created' => '✅ Transport `{transport_id}` created.',
            'error' => [
                'invalid_date' => '🕐 Date-time passed has invalid format, please use following format for date: YYYY-MM-DD, and for time: HH:MM',
                'too_far_date' => '🕐 Date-time passed is too far away from the event, you can only create transport 2 days before when going to the event or 2 days after when coming back from the event. Event is planned to start on {date_start} and finish on {date_end}.',
                'same_configuration' => '⛔ You already have created or joined a transport with the same configuration, you can’t have more than one transport per day and per direction.',
            ],
        ],
        'drop_traveler_from_transport' => [
            'description' => 'Drop a traveler from one of your transport',
            'ask_transport' => '🗑️ From which transport you wanna drop a traveler ?',
            'transport_button' => '{direction} at {hour} on {date}',
            'ask_traveler' => '🗑️ Which traveler you wanna drop from this transport ?',
            'traveler_button' => '{traveler_display_name}',
            'confirmation' => '🗑️ Are you sure you want to drop this traveler: {traveler_display_name}> ?',
            'confirm_button' => 'Drop this traveler',
            'confirm_label' => '🗑️ Traveler was dropped.',
            'dropped_traveler_dm' => 'The transport {direction} at {hour} on {date} was cancelled. You can still find a new one: <#{event_channel}> using `/search`',
            'cancel_button' => 'Cancel',
            'cancel_label' => '❌ Ignoring removal request.',
            'error' => [
                'no_transport' => '⛔ You have no transport(s) created for current channel’s event.',
                'no_traveler' => 'You have no travelers for this transport.',
            ],
        ],
        'join_transport' => [
            'description' => 'Join a transport as a passenger',
            'option' => [
                'transport' => 'ID of the transport you wanna join (taken from `/search` command)',
            ],
            'validation_direct' => '👤 You are now riding in transport `{transport_id}` (find more details about this transport with `/status` command).',
            'validation_dm' => 'Thanks for sharing a transport with <@{driver_id}>, get more details about the transport please send a DM to the transport driver: <@{driver_id}>',
            'validation_driver' => 'A new co-traveler joined your transport {direction} (at {hour} on {date}), they’ll be in touch with you or you can send them a DM: <@{traveler_id}>',
            'error' => [
                'no_transport' => '⛔ Could not find a transport for current channel event.',
                'created_transport' => '❌ You cannot join a transport you created.',
                'same_configuration' => '⛔ You already have created or joined a transport with the same configuration, you can’t have more than one transport per day and per direction.',
                'transport_full' => '👤 You cannot join this transport because its full 😔 Try another one !',
            ],
        ],
        'quit_transport' => [
            'description' => 'Quit a transport as a passenger',
            'travel_choice' => '🗑️ Which transport you wanna leave ?',
            'choice_button' => '{direction} at {hour} on {date}',
            'confirmation' => '🗑️ Are you sure you want to leave this transport ?',
            'confirm_button' => 'Leave',
            'confirm_label' => '🗑️ You left the transport !',
            'driver_dm' => 'Someone left your transport {direction} at {hour} on {date}. You now have {seats_remaining}/{seats_total} passenger seats available.',
            'cancel_button' => 'Cancel',
            'cancel_label' => '❌ Ignoring removal request.',
            'error' => [
                'no_transport' => '⛔ You have did not joined any transport for current channel’s event.',
            ],
        ],
        'remove_transport' => [
            'description' => 'Remove a transport you created for this event',
            'ask_remove' => '🗑️ Which transport you wanna remove ?',
            'button_label' => '{direction} at {hour} on {date}',
            'validation_remove' => '🗑️ Are you sure you want to delete your transport ?',
            'button_validation' => 'Delete',
            'label_validation' => '🗑️ Transport `{transport_id}` was removed.',
            'button_cancel' => 'Cancel',
            'label_cancel' => '❌ Ignoring removal request.',
            'removal_dm' => 'The transport {direction} at {hour} on {date} was cancelled. You can still find a new one: <#{event_channel}> using `/search`',
            'error' => [
                'no_transport' => '⛔ You have no transport(s) created for current channel’s event.',
            ],
        ],
        'search' => [
            'description' => 'Search a transport for a given postal code',
            'option' => [
                'postal_code' => 'Postal code you’re coming from on your way to the event or you’re going back to', // (it can be from 2 to 5 characters: 44 or 44430)
                'direction' => 'If you’re going to the event or coming back from it',
            ],
            'intro' => 'Transports found:',
            'row' => '- [`{transport_id}`] {direction} {postal_code} leaving at {hour} on {date} - {seats_remaining}/{seats_total} passenger seats available',
            'empty' => 'No transport found.',
        ],
        'status' => [
            'description' => 'List the transport(s) you’ve created or joined',
            'intro' => 'Your status for "{name}" event:',
            'row' => '- [{traveler_type}] Leaving at {hour} on {date} from {postal_code}',
            'row_not_driver' => ' (transported by <@{driver_id}>)',
            'row_driver' => ' ({seats_remaining}/{seats_total} passenger seats available, including {travelers})',
            'empty' => 'You have not registered in any transport for "{name}" event.',
        ],
    ],
];
