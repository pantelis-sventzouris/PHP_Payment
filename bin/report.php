<?php

declare(strict_types=1);

$container = require dirname(__DIR__) . '/bootstrap.php';

$options = getopt('', [
    'merchant:',
    'from::',
    'to::',
    'days::',
]);

if (!isset($options['merchant'])) {
    fwrite(
        STDERR,
        "Usage:\n" .
        "  php bin/report.php --merchant=1 --from=2026-09-01 --to=2026-09-18\n" .
        "  php bin/report.php --merchant=1 --days=7\n"
    );
    exit(1);
}

try {
    $merchantId = (int) $options['merchant'];

    if (isset($options['days'])) {
        $days = max(1, (int) $options['days']);
        $to = new DateTimeImmutable('now');
        $from = $to->modify("-{$days} days");
    } else {
        if (!isset($options['from'], $options['to'])) {
            throw new RuntimeException('Use either --days or both --from and --to.');
        }

        $from = new DateTimeImmutable($options['from'] . ' 00:00:00');
        $to = new DateTimeImmutable($options['to'] . ' 23:59:59');
    }

    $container['reportService']->send($merchantId, $from, $to);

    fwrite(STDOUT, "Charge report sent successfully.\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'Error: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
