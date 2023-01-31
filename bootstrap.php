<?php

declare(strict_types=1);

// Авто-загрузчик из vendor http-client-mitmproxy
$autoloaderPath = __DIR__ . '/vendor/autoload.php';
if (!file_exists($autoloaderPath)) {
    // Авто-загрузчик из vendor проекта, к которому mitmproxy подключен
    $autoloaderPath = dirname(__DIR__, 3) . '/vendor/autoload.php';
}

if (!file_exists($autoloaderPath)) {
    throw new RuntimeException('Can not find autoload.php');
}

require_once $autoloaderPath;