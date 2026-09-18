<?php

declare(strict_types=1);

use App\Application\ChargeReportService;
use App\Application\ChargeService;
use App\Http\Authenticator;
use App\Http\ChargeController;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Database\PdoChargeRepository;
use App\Infrastructure\Database\PdoMerchantRepository;
use App\Infrastructure\Mail\SymfonyMailer;
use App\Infrastructure\Payment\PaymentProviderFactory;

require __DIR__ . '/vendor/autoload.php';

$config = require __DIR__ . '/config/config.php';

$pdo = Connection::create($config['database_path']);

$merchantRepository = new PdoMerchantRepository($pdo);
$chargeRepository = new PdoChargeRepository($pdo);
$providerFactory = new PaymentProviderFactory();

$chargeService = new ChargeService($chargeRepository, $providerFactory);
$authenticator = new Authenticator($merchantRepository);

$chargeController = new ChargeController(
    $authenticator,
    $chargeService
);

$mailer = new SymfonyMailer(
    $config['mailer_dsn'],
    $config['mail_from']
);

$reportService = new ChargeReportService(
    $merchantRepository,
    $chargeRepository,
    $mailer
);

return [
    'pdo' => $pdo,
    'chargeController' => $chargeController,
    'reportService' => $reportService,
];
