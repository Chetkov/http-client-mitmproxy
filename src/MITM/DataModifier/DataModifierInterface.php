<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM\DataModifier;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface DataModifierInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return RequestInterface
     */
    public function modifyRequest(RequestInterface $request): RequestInterface;

    /**
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function modifyResponse(ResponseInterface $response): ResponseInterface;
}
