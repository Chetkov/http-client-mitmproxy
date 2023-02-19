<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\MITM\ProxyClient;
use Chetkov\HttpClientMitmproxy\MITM\ProxyUID;
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
     * @param ProxyUID $proxyUid
     * @param string $tempDir
     *
     * @return ProxyClient
     */
    public function createProxyClient(ProxyUID $proxyUid, string $tempDir): ProxyClient;

    /**
     * @param ProxyUID $proxyUid
     * @param ClientInterface $client
     *
     * @return ClientInterface
     */
    public function createHttpClientDecorator(ProxyUID $proxyUid, ClientInterface $client): ClientInterface;
}
