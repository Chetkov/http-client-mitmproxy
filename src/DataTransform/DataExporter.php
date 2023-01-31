<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform;

use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class DataExporter implements RequestExporterInterface, ResponseExporterInterface
{
    public function __construct(
        private RequestFormatterInterface $requestFormatter,
        private ResponseFormatterInterface $responseFormatter,
        private FormatConverterInterface $formatConverter,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function exportRequest(RequestInterface $request): string
    {
        return $this->formatConverter->convert($this->requestFormatter->toArray($request));
    }

    /**
     * @inheritDoc
     */
    public function importRequest(string $requestData): RequestInterface
    {
        return $this->requestFormatter->fromArray($this->formatConverter->reverse($requestData));
    }

    /**
     * @inheritDoc
     */
    public function exportResponse(ResponseInterface $response): string
    {
        return $this->formatConverter->convert($this->responseFormatter->toArray($response));
    }

    /**
     * @inheritDoc
     */
    public function importResponse(string $responseData): ResponseInterface
    {
        return $this->responseFormatter->fromArray($this->formatConverter->reverse($responseData));
    }
}
