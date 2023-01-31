<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\Response;

use Psr\Http\Message\ResponseInterface;

interface ResponseExporterInterface
{
    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    public function exportResponse(ResponseInterface $response): string;

    /**
     * @param string $responseData
     *
     * @return ResponseInterface
     */
    public function importResponse(string $responseData): ResponseInterface;
}
