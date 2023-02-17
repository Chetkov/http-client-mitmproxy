<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\RedisBasedCommunicationChannel;
use Chetkov\HttpClientMitmproxy\Console\ConsoleIOInterface;
use Chetkov\HttpClientMitmproxy\Console\SymfonyConsoleIOAdapter;
use Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter\CharsetConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter\CharsetConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterFactory;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\JSONFormatConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\PHPFormatConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\YAMLFormatConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatter;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatter;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use Chetkov\HttpClientMitmproxy\Editor\EditorInterface;
use Chetkov\HttpClientMitmproxy\Editor\FileBasedEditor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Exception\NotImplementedException;
use Chetkov\HttpClientMitmproxy\Helper\ArrayHelper;
use Chetkov\HttpClientMitmproxy\Helper\FileSystemHelper;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\DataModifierInterface;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\RealtimeDataModifier;
use Chetkov\HttpClientMitmproxy\MITM\Proxy;
use Chetkov\HttpClientMitmproxy\MITM\PsrClientMitmDecorator;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class DefaultRegistry implements RegistryInterface
{
    private array $MITMProxies = [];
    private array $communicationChannels = [];
    private array $editors = [];
    private array $dataModifiers = [];
    private array $formatConverters = [];
    private ?RequestFormatter $requestFormatter = null;
    private ?ResponseFormatter $responseFormatter = null;
    private ?ConsoleIOInterface $consoleIO = null;
    private ?FileSystemHelper $fileSystemHelper = null;
    private ?ArrayHelper $arrayHelper = null;
    private ?CharsetConverter $charsetConverter = null;

    /**
     * @param \Redis $redis
     * @param string $tempDir
     */
    public function __construct(
        private \Redis $redis,
        private string $tempDir = MITM_TEMP_DIR,
    ) {
    }

    /**
     * @param string $proxyUid
     * @param InputInterface $input
     *
     * @return Proxy
     */
    public function getProxy(string $proxyUid, InputInterface $input): Proxy
    {
        if (!isset($this->MITMProxies[$proxyUid])) {
            $this->MITMProxies[$proxyUid] = new Proxy(
                $this->getConsoleIO($input),
                new FormatConverterFactory($this),
                $this->getCommunicationChannel($proxyUid),
                $this->getEditor($proxyUid),
                $this->getArrayHelper(),
                $proxyUid,
            );
        }

        return $this->MITMProxies[$proxyUid];
    }

    /**
     * @inheritDoc
     */
    public function getDecoratedHttpClient(
        string $proxyUid,
        ClientInterface $originalClient,
    ): PsrClientMitmDecorator {
        return new PsrClientMitmDecorator(
            $this->getDataModifier($proxyUid),
            $originalClient,
        );
    }

    /**
     * @inheritDoc
     */
    public function getDataModifier(string $proxyUid): DataModifierInterface
    {
        if (!isset($this->dataModifiers[$proxyUid])) {
            $this->dataModifiers[$proxyUid] = new RealtimeDataModifier(
                $this->getCommunicationChannel($proxyUid),
                $this->getRequestFormatter(),
                $this->getResponseFormatter(),
                $this->getCharsetConverter(),
            );
        }
        return $this->dataModifiers[$proxyUid];
    }

    /**
     * @inheritDoc
     */
    public function getCommunicationChannel(string $proxyUid): CommunicationChannelInterface
    {
        if (!isset($this->communicationChannels[$proxyUid])) {
            $this->communicationChannels[$proxyUid] = new RedisBasedCommunicationChannel($this->redis, $proxyUid);
        }
        return $this->communicationChannels[$proxyUid];
    }

    /**
     * @param string $proxyUid
     *
     * @return EditorInterface
     */
    public function getEditor(string $proxyUid): EditorInterface
    {
        if (!isset($this->editors[$proxyUid])) {
            $this->editors[$proxyUid] = new FileBasedEditor(
                $this->getFileSystemHelper(),
                $proxyUid,
                $this->tempDir,
            );
        }

        return $this->editors[$proxyUid];
    }

    /**
     * @inheritDoc
     */
    public function getFormatConverter(Format $format): FormatConverterInterface
    {
        if (!isset($this->formatConverters[(string) $format])) {
            $this->formatConverters[(string) $format] = match (true) {
                $format->isYaml() => new YAMLFormatConverter(),
                $format->isJson() => new JSONFormatConverter(),
                $format->isPhp() => new PHPFormatConverter($this->tempDir),
                default => throw new NotImplementedException(),
            };
        }
        return $this->formatConverters[(string) $format];
    }

    /**
     * @inheritDoc
     */
    public function getRequestFormatter(): RequestFormatterInterface
    {
        if (!$this->requestFormatter) {
            $this->requestFormatter = new RequestFormatter();
        }
        return $this->requestFormatter;
    }

    /**
     * @inheritDoc
     */
    public function getResponseFormatter(): ResponseFormatterInterface
    {
        if (!$this->responseFormatter) {
            $this->responseFormatter = new ResponseFormatter();
        }
        return $this->responseFormatter;
    }

    /**
     * @inheritDoc
     */
    public function getCharsetConverter(): CharsetConverterInterface
    {
        if (!$this->charsetConverter) {
            $this->charsetConverter = new CharsetConverter();
        }
        return $this->charsetConverter;
    }

    /**
     * @inheritDoc
     */
    public function getConsoleIO(InputInterface $input): ConsoleIOInterface
    {
        if (!$this->consoleIO) {
            $this->consoleIO = new SymfonyConsoleIOAdapter(
                new SymfonyStyle($input, new ConsoleOutput()),
            );
        }
        return $this->consoleIO;
    }

    /**
     * @inheritDoc
     */
    public function getFileSystemHelper(): FileSystemHelper
    {
        if (!$this->fileSystemHelper) {
            $this->fileSystemHelper = new FileSystemHelper();
        }
        return $this->fileSystemHelper;
    }

    /**
     * @inheritDoc
     */
    public function getArrayHelper(): ArrayHelper
    {
        if (!$this->arrayHelper) {
            $this->arrayHelper = new ArrayHelper();
        }
        return $this->arrayHelper;
    }
}
