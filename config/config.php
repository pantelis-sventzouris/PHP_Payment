<?php

declare(strict_types=1);

return [
    'database_path' => getenv('DB_PATH') ?: dirname(__DIR__) . '/database/database.sqlite',
    'mailer_dsn' => getenv('MAILER_DSN') ?: 'smtp://127.0.0.1:1025',
    'mail_from' => getenv('MAIL_FROM') ?: 'reports@example.test',
];
