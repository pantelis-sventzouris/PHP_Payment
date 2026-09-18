<?php

declare(strict_types=1);

use App\Infrastructure\Database\Connection;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/config.php';

$pdo = Connection::create($config['database_path']);
$schema = file_get_contents(dirname(__DIR__) . '/database/schema.sql');

if ($schema === false) {
    fwrite(STDERR, "Could not read database/schema.sql\n");
    exit(1);
}

$pdo->exec($schema);

fwrite(STDOUT, "Database migrated successfully.\n");
