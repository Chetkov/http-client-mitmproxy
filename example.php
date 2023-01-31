<?php

declare(strict_types=1);

use Chetkov\HttpClientMitmproxy\DefaultRegistry;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

require_once __DIR__ . '/vendor/autoload.php';

$proxyUid = $_GET['MITM_PROXY_UID'] ?? getenv('MITM_PROXY_UID');
$format = $_GET['MITM_PROXY_FORMAT'] ?? getenv('MITM_PROXY_FORMAT');

//$nullWriter = new NullWriter();
//$consoleWriter = new ConsoleWriter($nullWriter);
//$fileWriter = new FileWriter($consoleWriter, __DIR__ . '/storage/' . $proxyUid . '/dump.tmp');
//$datedWriter = new DatedWriter($fileWriter, 'H:i:s');
//$eolWriter = new EOLWriter($datedWriter);

$originalClient = new Client();

$redis = new \Redis();
$redis->connect('localhost');
$registry = new DefaultRegistry($redis);

$mitmClient = $registry->getHttpClientMitmproxyDecorator($proxyUid, Format::fromString($format), $originalClient);

echo 'send'.PHP_EOL;
$response = $mitmClient->sendRequest(new Request('GET', 'https://vk.com/?id=150#filarmony1'));

echo $response->getStatusCode() . ' ' . $response->getBody()->getContents() . PHP_EOL . PHP_EOL;

echo 'send'.PHP_EOL;
$response = $mitmClient->sendRequest(new Request('GET', 'https://vk.com/?id=151#filarmony2'));

echo $response->getStatusCode() . ' ' . $response->getBody()->getContents() . PHP_EOL . PHP_EOL;

echo 'send'.PHP_EOL;
$response = $mitmClient->sendRequest(new Request('GET', 'https://vk.com/?id=152#filarmony3'));

echo $response->getStatusCode() . ' ' . $response->getBody()->getContents() . PHP_EOL . PHP_EOL;

echo 'send'.PHP_EOL;
$response = $mitmClient->sendRequest(new Request('GET', 'https://vk.com/?id=153#filarmony4'));

echo $response->getStatusCode() . ' ' . $response->getBody()->getContents() . PHP_EOL . PHP_EOL;

$f = 1;