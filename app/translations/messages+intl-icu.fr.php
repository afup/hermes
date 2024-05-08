<?php

declare(strict_types=1);

return [
    'enum' => [
        'event' => 'Vers l\'évènement',
        'event_with_postal_code' => 'depuis {postal_code} vers l\'évènement',
        'home' => 'Vers ma maison',
        'home_with_postal_code' => 'vers l\'évènemet depuis {postal_code}',
    ],
    'command' => [
        'create_event' => [
            'event_name' => 'Quel est le nom de l\'évènement ?',
            'channel_id' => 'Quel est l\'ID du canal où le bot va opérer ?',
            'start_date' => 'Quand est-ce que l\'évènment commence ? (format: YYYY-MM-DD)',
            'finish_date' => 'Quand est-ce que l\'évènment fini ? (format: YYYY-MM-DD)',
            'created' => 'Évènement crée `{name}`',
            'error' => [
                'event_name' => 'Le nom de l\'évènement doit être une chaîne de caractères et avoir au moins 3 caractères.',
                'name_exists' => 'Un évènement avec le même nom existe déjà, utilisez un autre nom !',
                'channel_id' => 'L\'ID du canal de l\'évènement doit être un entier.',
                'date_format' => 'Date incorrecte, merci de donner une date qui response le format suivant: 2024-04-13 [YYYY-MM-DD].',
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
            'description' => 'Créer un nouveau transport pour l\'évènement',
            'option' => [
                'passenger_seats' => 'Nombre de places passager disponibles',
                'postal_code' => 'Code postal d\'où vous partez vers l\'évènement ou vous retournez',
                'when_date' => 'Le jour de votre transport (format: YYYY-MM-DD)',
                'when_time' => 'L\'heure de votre transport (format: HH:MM)',
            ],
            'ask_direction' => '🚙 Est-ce que vous allez vers l\'évènement ou vous en revenez ?',
            'created' => '✅ Transport `{transport_id}` crée.',
            'error' => [
                'invalid_date' => '🕐 La date et/ou l\'heure donné n\'utilise pas un format valide, merci d\'utiliser le format suivant pour la date: YYYY-MM-DD, et celui-ci pour l\'heure: HH:MM',
                'same_configuration' => '⛔ Vous avez déjà crée ou rejoins un transport avec la même configuration, vous ne pouvez avoir qu\'un seul transport par jour par direction.',
            ],
        ],
        'drop_traveler_from_transport' => [
            'description' => 'Retirer un voyageur d\'un de vos transport(s)',
            'ask_transport' => '🗑️ De quel transport voulez-vous retirer un voyageur ?',
            'transport_button' => '{direction} à {date}',
            'ask_traveler' => '🗑️ Quel passager voulez-vous retirer de ce transport ?',
            'traveler_button' => '{traveler_display_name}',
            'confirmation' => '🗑️ Êtes-vous sûr que vous voulez retirer le passager suivant: {traveler_display_name}> ?',
            'confirm_button' => 'Retirer ce voyageur',
            'confirm_label' => '🗑️ Le voyageur a été retiré.',
            'dropped_traveler_dm' => 'Le transport {direction} à {date} a été annulé. Vous pouvez en trouver un nouveau sur le canal: <#{event_channel}> en utilisant `/search`',
            'cancel_button' => 'Annuler',
            'cancel_label' => '❌ Demande annulée.',
            'error' => [
                'no_transport' => '⛔ Vous n\'avez aucun transport(s) crée pour l\'évènement de ce canal.',
                'no_traveler' => 'Vous n\'avez pas de passagers pour ce transport.',
            ],
        ],
        'join_transport' => [
            'description' => 'Rejoindre un transport en tant que passager',
            'option' => [
                'transport' => 'ID du transport que vous voulez rejoindre (pris de la commande `/search`)',
            ],
            'validation_direct' => '👤 Vous êtes maintenant passager du transport `{transport_id}` (récupérez plus de détails à propos de ce transport avec la commande `/status`).',
            'validation_dm' => 'Merci de partager un transport avec <@{driver_id}>, pour récupérer plus d\'informations à propos du transport, envoyez un DM au conducteur: <@{driver_id}>',
            'validation_driver' => 'Un nouveau passager a rejoins votre transport {direction} (à {date}), il va probablement vous contacter mais vous pouvez aussi lui envoyer un DM: <@{traveler_id}>',
            'error' => [
                'no_transport' => '⛔ Vous n\'avez aucun transport(s) crée pour l\'évènement de ce canal.',
                'created_transport' => '❌ Vous ne pouvez pas rejoindre un transport que vous avez crée.',
                'same_configuration' => '⛔ Vous avez déjà crée ou rejoins un transport avec la même configuration, vous ne pouvez avoir qu\'un seul transport par jour par direction.',
            ],
        ],
        'quit_transport' => [
            'description' => 'Partir d\'un transport en tant que passager',
            'travel_choice' => '🗑️ De quel transport voulez-vous partir ?',
            'choice_button' => '{direction} à {date}',
            'confirmation' => '🗑️ Êtes vous sûr que vous voulez vous retirer ce transport ?',
            'confirm_button' => 'Se retirer du transport',
            'confirm_label' => '🗑️ Vous êtes parti de ce transport !',
            'driver_dm' => 'Quelqu\'un est parti de votre transport {direction} à {date}. Vous avez maintenant {seats_remaining}/{seats_total} places disponibles.',
            'cancel_button' => 'Annuler',
            'cancel_label' => '❌ Demande annulée.',
            'error' => [
                'no_transport' => '⛔ Vous n\'avez aucun transport(s) que vous avez rejoins pour l\'évènement de ce canal.',
            ],
        ],
        'remove_transport' => [
            'description' => 'Supprimer un transport que vous avez crée pour cet évènement',
            'ask_remove' => '🗑️ Quel transport voulez-vous supprimer ?',
            'button_label' => '{direction} à {date}',
            'validation_remove' => '🗑️ Êtes vous sûr de vouloir supprimer votre transport ?',
            'button_validation' => 'Supprimer',
            'label_validation' => '🗑️ Le transport `{transport_id}` a été supprimé.',
            'button_cancel' => 'Annuler',
            'label_cancel' => '❌ Demande annulée.',
            'removal_dm' => 'Le transport {direction} à {date} a été annulé. Vous pouvez toujours trouver un nouveau transport via le canal: <#{event_channel}> en utilisant la commande `/search`',
            'error' => [
                'no_transport' => '⛔ Vous n\'avez aucun transport(s) que vous avez crée pour l\'évènement de ce canal.',
            ],
        ],
        'search' => [
            'description' => 'Rechercher un transport pour un code postal donné',
            'option' => [
                'postal_code' => 'Code postal d\'où vous partez vers l\'évènement ou vous retournez (44 ou 44430)',
                'direction' => 'Si vous aller vers l\'évènement ou si vous en revenez',
            ],
            'intro' => 'Transports trouvés:',
            'row' => '- [`{transport_id}`] {direction} {postal_code} part à {date} - {seats_remaining}/{seats_total} places disponibles',
            'empty' => 'Aucun transport trouvé.',
        ],
        'status' => [
            'description' => 'Liste des transport que vous avez crée ou rejoins',
            'intro' => 'Votre statut pour l\'évènement "{name}":',
            'row' => '- [{traveler_type}] Part à {date} depuis {postal_code}',
            'row_not_driver' => ' (conduit par <@{driver_id}>)',
            'row_driver' => ' ({seats_remaining}/{seats_total} places disponibles)',
            'empty' => 'Vous n\'êtes enregistré dans aucun transport pour l\'évènement "{name}".',
        ],
    ],
];
