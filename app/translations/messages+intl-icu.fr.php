<?php

declare(strict_types=1);

return [
    'enum' => [
        'event' => 'Vers l’événement',
        'event_with_postal_code' => 'depuis {postal_code} vers l’événement',
        'home' => 'Vers ma maison',
        'home_with_postal_code' => 'vers l’évènemet depuis {postal_code}',
    ],
    'command' => [
        'create_event' => [
            'event_name' => 'Quel est le nom de l’événement ?',
            'channel_id' => 'Quel est l’ID du canal où le bot va opérer ?',
            'start_date' => 'Quand est-ce que l’événement commence ? (format: YYYY-MM-DD)',
            'finish_date' => 'Quand est-ce que l’événement finit ? (format: YYYY-MM-DD)',
            'created' => 'Événement créé `{name}`',
            'error' => [
                'event_name' => 'Le nom de l’événement doit être une chaîne de caractères et avoir au moins 3 caractères.',
                'name_exists' => 'Un événement avec le même nom existe déjà, utilisez un autre nom !',
                'channel_id' => 'L’ID du canal de l’événement doit être un entier.',
                'date_format' => 'Date incorrecte, merci de donner une date qui correspond au format suivant: 2024-04-13 [YYYY-MM-DD].',
                'finish_date' => 'Date incorrecte, merci de donner une date de fin qui est la même ou est plus grande que la date de début.',
            ],
        ],
        'register' => [
            'to_clean' => '{commands, plural,
                =0    {Aucune commande slash à supprimer ...}
                =1    {Une commande slash à supprimer ...}
                other {# commandes slash à supprimer ...}
            }',
            'cleaning' => 'Suppression de la commande slash: /{command} [{id}]',
            'to_register' => '{commands, plural,
                =0    {Aucune commande slash à enregistrer ...}
                =1    {Une commande slash à enregistrer ...}
                other {# commandes slash à enregistrer ...}
            }',
            'register' => 'Enregistrement de la commande slash: /{command}',
            'success' => 'Les commandes slash ont été enregistrées avec succès ! ✅',
            'error' => [
                'no_slash_commands' => 'Aucune commande slash à supprimer.',
            ],
        ],
    ],
    'discord' => [
        'create_transport' => [
            'description' => 'Créer un nouveau transport pour l’événement',
            'option' => [
                'passenger_seats' => 'Nombre de places passagers disponibles',
                'postal_code' => 'Code postal d’où vous partez vers l’événement ou vous retournez',
                'when_date' => 'Le jour de votre transport (format: YYYY-MM-DD)',
                'when_time' => 'L’heure de votre transport (format: HH:MM)',
            ],
            'ask_direction' => '🚙 Est-ce que vous allez vers l’événement ou vous en revenez ?',
            'created' => '✅ Transport `{transport_id}` crée.',
            'error' => [
                'invalid_date' => '🕐 La date et/ou l’heure donnée(s) n’utilise pas un format valide, merci d’utiliser le format suivant pour la date: YYYY-MM-DD, et celui-ci pour l’heure: HH:MM',
                'same_configuration' => '⛔ Vous avez déjà créé ou rejoint un transport avec la même configuration, vous ne pouvez avoir qu’un seul transport par jour par direction.',
            ],
        ],
        'drop_traveler_from_transport' => [
            'description' => 'Retirer un voyageur ou une voyageuse d’un de vos transport(s)',
            'ask_transport' => '🗑️ De quel transport voulez-vous retirer un un voyageur ou une voyageuse ?',
            'transport_button' => '{direction} à {hour} le {date}',
            'ask_traveler' => '🗑️ Quel passager ou passagère voulez-vous retirer de ce transport ?',
            'traveler_button' => '{traveler_display_name}',
            'confirmation' => '🗑️ Êtes-vous sûr·e que vous voulez retirer le/la passager·e suivant·e: {traveler_display_name}> ?',
            'confirm_button' => 'Retirer ce·tte voyageur·euse',
            'confirm_label' => '🗑️ La personne a été retirée',
            'dropped_traveler_dm' => 'Le transport {direction} à {hour} le {date} a été annulé. Vous pouvez en trouver un nouveau sur le canal: <#{event_channel}> en utilisant `/search`',
            'cancel_button' => 'Annuler',
            'cancel_label' => '❌ Demande annulée.',
            'error' => [
                'no_transport' => '⛔ Vous n’avez aucun transport créé pour l’événement de ce canal.',
                'no_traveler' => 'Vous n’avez pas de passagers pour ce transport.',
            ],
        ],
        'join_transport' => [
            'description' => 'Rejoindre un transport en tant que passager·e',
            'option' => [
                'transport' => 'ID du transport que vous voulez rejoindre (pris de la commande `/search`)',
            ],
            'validation_direct' => '👤 Vous êtes maintenant passager·e du transport `{transport_id}` (récupérez plus de détails à propos de ce transport avec la commande `/status`).',
            'validation_dm' => 'Merci de partager un transport avec <@{driver_id}>, pour récupérer plus d’informations à propos du transport, envoyez-lui un DM: <@{driver_id}>',
            'validation_driver' => 'Une nouvelle personne a rejoint votre transport {direction} (à {hour} le {date}), elle va probablement vous contacter mais vous pouvez aussi lui envoyer un DM: <@{traveler_id}>',
            'error' => [
                'no_transport' => '⛔ Vous n’avez aucun transport créé pour l’événement de ce canal.',
                'created_transport' => '❌ Vous ne pouvez pas rejoindre un transport que vous avez créé.',
                'same_configuration' => '⛔ Vous avez déjà créé ou rejoint un transport avec la même configuration, vous ne pouvez avoir qu’un seul transport par jour par direction.',
            ],
        ],
        'quit_transport' => [
            'description' => 'Partir d’un transport en tant que passager·e',
            'travel_choice' => '🗑️ De quel transport voulez-vous partir ?',
            'choice_button' => '{direction} à {hour} le {date}',
            'confirmation' => '🗑️ Êtes vous sûr·e que vous voulez vous retirer ce transport ?',
            'confirm_button' => 'Se retirer du transport',
            'confirm_label' => '🗑️ Vous avez quitté ce transport !',
            'driver_dm' => 'Quelqu’un est parti de votre transport {direction} à {hour} le {date}. Vous avez maintenant {seats_remaining}/{seats_total} places disponibles.',
            'cancel_button' => 'Annuler',
            'cancel_label' => '❌ Demande annulée.',
            'error' => [
                'no_transport' => '⛔ Vous n’avez rejoint aucun transport pour l’événement de ce canal.',
            ],
        ],
        'remove_transport' => [
            'description' => 'Supprimer un transport que vous avez créé pour cet événement',
            'ask_remove' => '🗑️ Quel transport voulez-vous supprimer ?',
            'button_label' => '{direction} à {hour} le {date}',
            'validation_remove' => '🗑️ Êtes vous sûr·e de vouloir supprimer votre transport ?',
            'button_validation' => 'Supprimer',
            'label_validation' => '🗑️ Le transport `{transport_id}` a été supprimé.',
            'button_cancel' => 'Annuler',
            'label_cancel' => '❌ Demande annulée.',
            'removal_dm' => 'Le transport {direction} à {hour} le {date} a été annulé. Vous pouvez toujours trouver un nouveau transport via le canal: <#{event_channel}> en utilisant la commande `/search`',
            'error' => [
                'no_transport' => '⛔ Vous n’avez aucun transport créé pour l’événement de ce canal.',
            ],
        ],
        'search' => [
            'description' => 'Rechercher un transport pour un code postal donné',
            'option' => [
                'postal_code' => 'Code postal d’où vous partez vers l’événement ou vous retournez (44 ou 44430)',
                'direction' => 'Si vous aller vers l’événement ou si vous en revenez',
            ],
            'intro' => 'Transports trouvés:',
            'row' => '- [`{transport_id}`] {direction} {postal_code} part à {hour} le {date} - {seats_remaining}/{seats_total} places disponibles',
            'empty' => 'Aucun transport trouvé.',
        ],
        'status' => [
            'description' => 'Liste des transports que vous avez créés ou rejoints',
            'intro' => 'Votre statut pour l’événement "{name}":',
            'row' => '- [{traveler_type}] Part à {hour} le {date} depuis {postal_code}',
            'row_not_driver' => ' (conduit par <@{driver_id}>)',
            'row_driver' => ' ({seats_remaining}/{seats_total} places disponibles)',
            'empty' => 'Vous n’êtes enregistré·e dans aucun transport pour l’événement "{name}".',
        ],
    ],
];
