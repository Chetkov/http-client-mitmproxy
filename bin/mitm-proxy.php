<?php

declare(strict_types=1);
declare(ticks = 1);

use Chetkov\HttpClientMitmproxy\MITMProxy;
use Chetkov\HttpClientMitmproxy\RegistryInterface;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$configPath = $argv[1] ?? MITM_CONFIG_DIR . '/mitm-proxy-config.php';
$config = require $configPath;

/** @var RegistryInterface $registry */
$registry = $config['registry_factory']();

$proxyUid = md5(uniqid((string) getmypid(), true));

(new MITMProxy(
    $registry->getFileSystemHelper(),
    $registry->getConsoleIO(),
    $registry->getCommunicationChannel($proxyUid),
    $proxyUid,
))->start();
