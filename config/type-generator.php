<?php

return [
    'migrations_path' => 'database/migrations',
    'requests_path' => 'app/Http/Requests',
    'destination_folder' => 'resources/js/types',
    'blacklist' => [
        '0001_01_01_000000_create_users_table.php', '0001_01_01_000001_create_cache_table.php', '0001_01_01_000002_create_jobs_table.php', '2024_11_14_104342_create_webauthn_credentials.php',
    ],
    'type_map' => [
        'string' => 'string',
        'number' => 'number',
        'boolean' => 'boolean',
        'file' => 'File',
        'any' => 'any',
    ],
];
