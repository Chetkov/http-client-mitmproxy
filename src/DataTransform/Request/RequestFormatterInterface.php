<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\Request;

use Psr\Http\Message\RequestInterface;

interface RequestFormatterInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return array
     */
    public function toArray(RequestInterface $request): array;

    /**
     * @param array $requestData
     *
     * @return RequestInterface
     */
    public function fromArray(array $requestData): RequestInterface;
}
