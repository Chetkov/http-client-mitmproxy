<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\Request;

use Psr\Http\Message\RequestInterface;

interface RequestExporterInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    public function exportRequest(RequestInterface $request): string;

    /**
     * @param string $requestData
     *
     * @return RequestInterface
     */
    public function importRequest(string $requestData): RequestInterface;
}
