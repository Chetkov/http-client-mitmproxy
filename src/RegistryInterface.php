<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\FileSystem\FileSystemHelper;
use Chetkov\HttpClientMitmproxy\MITM\DataModifier\DataModifierInterface;
use Chetkov\HttpClientMitmproxy\MITM\HttpClientMITMDecorator;
use Psr\Http\Client\ClientInterface;

interface RegistryInterface
{
    /**
     * @param string $proxyUid
     * @param Format $format
     * @param ClientInterface $originalClient
     *
     * @return HttpClientMITMDecorator
     */
    public function getHttpClientMitmproxyDecorator(string $proxyUid, Format $format, ClientInterface $originalClient): HttpClientMITMDecorator;

    /**
     * @param string $proxyUid
     * @param Format $format
     *
     * @return DataModifierInterface
     */
    public function getDataModifier(string $proxyUid, Format $format): DataModifierInterface;

    /**
     * @param string $proxyUid
     *
     * @return CommunicationChannelInterface
     */
    public function getCommunicationChannel(string $proxyUid): CommunicationChannelInterface;

    /**
     * @param Format $format
     *
     * @return RequestExporterInterface
     */
    public function getRequestExporter(Format $format): RequestExporterInterface;

    /**
     * @param Format $format
     *
     * @return ResponseExporterInterface
     */
    public function getResponseExporter(Format $format): ResponseExporterInterface;

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
     * @return FileSystemHelper
     */
    public function getFileSystemHelper(): FileSystemHelper;
}