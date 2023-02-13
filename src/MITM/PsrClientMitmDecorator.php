<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM;

use Chetkov\HttpClientMitmproxy\MITM\DataModifier\DataModifierInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class PsrClientMitmDecorator implements ClientInterface
{
    /**
     * @param DataModifierInterface $modifier
     * @param ClientInterface $httpClient
     */
    public function __construct(
        private DataModifierInterface $modifier,
        private ClientInterface $httpClient,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $request = $this->modifier->modifyRequest($request);

        $response = $this->httpClient->sendRequest($request);

        return $this->modifier->modifyResponse($response);
    }
}
