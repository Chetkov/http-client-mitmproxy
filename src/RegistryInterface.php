<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Console\ConsoleIOInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter\CharsetConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Helper\ArrayHelper;
use Chetkov\HttpClientMitmproxy\Helper\FileSystemHelper;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\DataModifierInterface;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\Console\Input\InputInterface;

interface RegistryInterface
{
    /**
     * @param string $proxyUid
     * @param ClientInterface $originalClient
     *
     * @return ClientInterface
     */
    public function getDecoratedHttpClient(string $proxyUid, ClientInterface $originalClient): ClientInterface;

    /**
     * @param string $proxyUid
     *
     * @return DataModifierInterface
     */
    public function getDataModifier(string $proxyUid): DataModifierInterface;

    /**
     * @param string $proxyUid
     *
     * @return CommunicationChannelInterface
     */
    public function getCommunicationChannel(string $proxyUid): CommunicationChannelInterface;

    /**
     * @param Format $format
     *
     * @return FormatConverterInterface
     */
    public function getFormatConverter(Format $format): FormatConverterInterface;

    /**
     * @return RequestFormatterInterface
     */
    public function getRequestFormatter(): RequestFormatterInterface;

    /**
     * @return ResponseFormatterInterface
     */
    public function getResponseFormatter(): ResponseFormatterInterface;

    /**
     * @return CharsetConverterInterface
     */
    public function getCharsetConverter(): CharsetConverterInterface;

    /**
     * @param InputInterface $input
     *
     * @return ConsoleIOInterface
     */
    public function getConsoleIO(InputInterface $input): ConsoleIOInterface;

    /**
     * @return FileSystemHelper
     */
    public function getFileSystemHelper(): FileSystemHelper;

    /**
     * @return ArrayHelper
     */
    public function getArrayHelper(): ArrayHelper;
}
