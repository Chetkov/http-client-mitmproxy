<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\RedisBasedCommunicationChannel;
use Chetkov\HttpClientMitmproxy\Console\SymfonyConsoleIOAdapter;
use Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter\CharsetConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterFactory;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatter;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatter;
use Chetkov\HttpClientMitmproxy\Editor\FileBasedEditor;
use Chetkov\HttpClientMitmproxy\Helper\ArrayHelper;
use Chetkov\HttpClientMitmproxy\Helper\FileSystemHelper;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\RealtimeDataModifier;
use Chetkov\HttpClientMitmproxy\MITM\ProxyClient;
use Chetkov\HttpClientMitmproxy\MITM\PsrClientMitmDecorator;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class DefaultFactory implements MitmProxyFactoryInterface
{
    /**
     * @param array{redis: array{host: string, port: int, timeout: int}} $config
     */
    public function __construct(
        private array $config,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function reconfigure(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param string $proxyUid
     * @param string $tempDir
     *
     * @return ProxyClient
     *
     * @throws \RedisException
     */
    public function createProxyClient(string $proxyUid, string $tempDir): ProxyClient
    {
        $tempDir = "$tempDir/$proxyUid";

        return new ProxyClient(
            new SymfonyConsoleIOAdapter(new SymfonyStyle(new ArgvInput(), new ConsoleOutput())),
            new FormatConverterFactory($tempDir),
            $this->createCommunicationChannel($proxyUid),
            new FileBasedEditor(new FileSystemHelper(), $tempDir),
            new ArrayHelper(),
            $proxyUid
        );
    }

    /**
     * @param string $proxyUid
     * @param ClientInterface $client
     *
     * @return ClientInterface
     *
     * @throws \RedisException
     */
    public function createHttpClientDecorator(string $proxyUid, ClientInterface $client): ClientInterface
    {
        return new PsrClientMitmDecorator(
            new RealtimeDataModifier(
                $this->createCommunicationChannel($proxyUid),
                new RequestFormatter(),
                new ResponseFormatter(),
                new CharsetConverter(),
            ),
            $client,
        );
    }

    /**
     * @param string $proxyUid
     *
     * @return CommunicationChannelInterface
     *
     * @throws \RedisException
     */
    protected function createCommunicationChannel(string $proxyUid): CommunicationChannelInterface
    {
        $redisConfig = $this->config['redis'];

        $redis = new \Redis();
        $redis->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']);

        return new RedisBasedCommunicationChannel($redis, $proxyUid);
    }
}
