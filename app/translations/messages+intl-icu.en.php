<?php

declare(strict_types=1);

return [
    'enum' => [
        'event' => 'To the event',
        'home' => 'To my place',
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
                'seats' => 'Number of seats available for other travelers',
                'postal_code' => 'Postal code you\'re coming from or you\'re going to',
                'when' => 'When you are starting your trip (format: YYYY-MM-DD HH:MM:SS)',
            ],
            'ask_direction' => '🚙 Are you going to the event or coming back to your place ?',
            'created' => '✅ Transport `{transport_id}` created.',
            'error' => [
                'invalid_date' => '🕐 Date-time passed has invalid format, please use following format: YYYY-MM-DD HH:MM:SS',
                'same_configuration' => '⛔ You already have created a transport with the same configuration, you can\'t have more than one transport per day and per direction.',
            ],
        ],
        'drop_traveler_from_transport' => [
            'description' => 'Drop a traveler from one of your transport',
            'ask_transport' => '🗑️ From which transport you wanna drop a traveler ?',
            'transport_button' => '[{direction}] {date}',
            'ask_traveler' => '🗑️ Which traveler you wanna drop from this transport ?',
            'traveler_button' => '<@{traveler_id}>',
            'confirmation' => '🗑️ Are you sure you want to drop this traveler: <@{traveler_id}> ?',
            'confirm_button' => 'Drop this traveler',
            'confirm_label' => '🗑️ Traveler was dropped.',
            'cancel_button' => 'Cancel',
            'cancel_label' => '❌ Ignoring removal request.',
            'error' => [
                'no_transport' => '⛔ You have no transport(s) created for current channel\'s event.',
            ],
        ],
        'join_transport' => [
            'description' => 'Join a transport as a passenger',
            'option' => [
                'transport' => 'ID of the transport you wanna join (taken from /search command)',
            ],
            'validation_direct' => '👤 You are now riding in Transport `{transport_id}`.',
            'validation_dm' => 'Thanks for sharing a ride with <@{driver_id}>, if you want more details about the transport please send DM to the transport creator: <@{driver_id}>',
            'validation_driver_direction_event' => 'from {postal_code} to the event',
            'validation_driver_direction_home' => 'from the event to {postal_code}',
            'validation_driver' => 'A new co-traveler joined your transport {direction} ({date}), you can send him a message: <@{traveler_id}>',
            'error' => [
                'no_transport' => '⁉️ Could not find a Transport for current channel event.',
            ],
        ],
        'leave_transport' => [
            'description' => 'Leave a transport as a passenger',
            'travel_choice' => '🗑️ Which travel you wanna leave ?',
            'choice_button' => '[{direction}] {date}',
            'confirmation' => '🗑️ Are you sure you want to leave this travel ?',
            'confirm_button' => 'Leave',
            'confirm_label' => '🗑️ You left the travel !',
            'cancel_button' => 'Cancel',
            'cancel_label' => '❌ Ignoring removal request.',
            'error' => [
                'no_transport' => '⛔ You have did not joined any transport for current channel\'s event.',
            ],
        ],
        'remove_transport' => [
            'description' => 'Remove the transport you created for the event',
            'ask_remove' => '🗑️ Which transport you wanna remove ?',
            'button_label' => '[{direction}] {date}',
            'validation_remove' => '🗑️ Are you sure you want to delete your transport ?',
            'button_validation' => 'Delete',
            'label_validation' => '🗑️ Transport `{transport_id}` was removed.',
            'button_cancel' => 'Cancel',
            'label_cancel' => '❌ Ignoring removal request.',
            'error' => [
                'no_transport' => '⛔ You have no transport(s) created for current channel\'s event.',
            ],
        ],
        'search' => [
            'description' => 'Search a transport for a given postal code',
            'option' => [
                'postal_code' => 'Postal code you\'re coming from or you\'re going to',
                'direction' => 'If you\'re going to the event or coming back from it',
            ],
            'intro' => 'Transports found:',
            'row' => '- [`{transport_id}`] {direction} {postal_code} leaving at {date} - {seats_remaining}/{seats_total} seats available leaving',
        ],
        'status' => [
            'description' => 'Your current status within the current channel event',
            'intro' => 'Your status for "{name}" event:',
            'row' => '- [{traveler_type}] Leaving at {date} from {postal_code}',
            'row_not_driver' => ' (created by <@{driver_id}>)',
            'empty' => 'You have not registered in any transport for "{name}" event.',
        ],
    ],
];
