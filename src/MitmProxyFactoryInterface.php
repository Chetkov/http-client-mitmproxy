<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\MITM\ProxyClient;
use Psr\Http\Client\ClientInterface;

interface MitmProxyFactoryInterface
{
    /**
     * @param array $config
     *
     * @return void
     */
    public function reconfigure(array $config): void;

    /**
     * @param string $proxyUid
     * @param string $tempDir
     *
     * @return ProxyClient
     */
    public function createProxyClient(string $proxyUid, string $tempDir): ProxyClient;

    /**
     * @param string $proxyUid
     * @param ClientInterface $client
     *
     * @return ClientInterface
     */
    public function createHttpClientDecorator(string $proxyUid, ClientInterface $client): ClientInterface;
}
