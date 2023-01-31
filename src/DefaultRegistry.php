<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\RedisBasedCommunicationChannel;
use Chetkov\HttpClientMitmproxy\Console\ConsoleIOInterface;
use Chetkov\HttpClientMitmproxy\Console\SymfonyConsoleIOAdapter;
use Chetkov\HttpClientMitmproxy\DataTransform\DataExporter;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\JSONFormatConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\PHPFormatConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\YAMLFormatConverter;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatter;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatter;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Exception\NotImplementedException;
use Chetkov\HttpClientMitmproxy\FileSystem\FileSystemHelper;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\DataModifierInterface;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\RealtimeDataModifier;
use Chetkov\HttpClientMitmproxy\MITM\HttpClientMITMDecorator;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class DefaultRegistry implements RegistryInterface
{
    private array $communicationChannels = [];
    private array $dataModifiers = [];
    private array $dataExporters = [];
    private array $formatConverters = [];
    private ?RequestFormatter $requestFormatter = null;
    private ?ResponseFormatter $responseFormatter = null;
    private ?ConsoleIOInterface $consoleIO = null;
    private ?FileSystemHelper $fileSystemHelper = null;

    /**
     * @param \Redis $redis
     */
    public function __construct(
        private \Redis $redis,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws \RedisException
     */
    public function getHttpClientMitmproxyDecorator(
        string $proxyUid,
        Format $format,
        ClientInterface $originalClient,
    ): HttpClientMITMDecorator {
        return new HttpClientMITMDecorator(
            $this->getDataModifier($proxyUid, $format),
            $originalClient,
        );
    }

    /**
     * @inheritDoc
     *
     * @throws \RedisException
     */
    public function getDataModifier(string $proxyUid, Format $format): DataModifierInterface
    {
        if (!isset($this->dataModifiers[$proxyUid][(string) $format])) {
            $this->dataModifiers[$proxyUid][(string) $format] = new RealtimeDataModifier(
                $this->getCommunicationChannel($proxyUid),
                $this->getRequestExporter($format),
                $this->getResponseExporter($format),
            );
        }
        return $this->dataModifiers[$proxyUid][(string) $format];
    }

    /**
     * @inheritDoc
     *
     * @throws \RedisException
     */
    public function getCommunicationChannel(string $proxyUid): CommunicationChannelInterface
    {
        if (!isset($this->communicationChannels[$proxyUid])) {
            $this->communicationChannels[$proxyUid] = new RedisBasedCommunicationChannel($this->redis, $proxyUid);
        }
        return $this->communicationChannels[$proxyUid];
    }

    /**
     * @inheritDoc
     */
    public function getRequestExporter(Format $format): RequestExporterInterface
    {
        return $this->getDataExporter($format);
    }

    /**
     * @inheritDoc
     */
    public function getResponseExporter(Format $format): ResponseExporterInterface
    {
        return $this->getDataExporter($format);
    }

    /**
     * @param Format $format
     *
     * @return DataExporter
     */
    private function getDataExporter(Format $format): DataExporter
    {
        if (!isset($this->dataExporters[(string) $format])) {
            $this->dataExporters[(string) $format] = new DataExporter(
                $this->getRequestFormatter(),
                $this->getResponseFormatter(),
                $this->getFormatConverter($format),
            );
        }
        return $this->dataExporters[(string) $format];
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
                $format->isPhp() => new PHPFormatConverter(),
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

    public function getConsoleIO(): ConsoleIOInterface
    {
        if (!$this->consoleIO) {
            $this->consoleIO = new SymfonyConsoleIOAdapter(new SymfonyStyle(new ArgvInput(), new ConsoleOutput()));
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
}
