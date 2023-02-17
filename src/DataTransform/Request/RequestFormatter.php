<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\Request;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;

class RequestFormatter implements RequestFormatterInterface
{
    private const
        FIELD_PROTOCOL_VERSION = 'protocol_version',
        FIELD_METHOD = 'method',
        FIELD_URI = 'uri',
        FIELD_URI_SCHEMA = 'schema',
        FIELD_URI_USER = 'user',
        FIELD_URI_PASS = 'pass',
        FIELD_URI_HOST = 'host',
        FIELD_URI_PORT = 'port',
        FIELD_URI_PATH = 'path',
        FIELD_URI_QUERY = 'query',
        FIELD_URI_FRAGMENT = 'fragment',
        FIELD_HEADERS = 'headers',
        FIELD_BODY = 'body';

    /**
     * @inheritDoc
     */
    public function toArray(RequestInterface $request): array
    {
        $uri = $request->getUri();

        $userInfo = explode(':', $uri->getUserInfo());

        return [
            self::FIELD_PROTOCOL_VERSION => $request->getProtocolVersion(),
            self::FIELD_METHOD => $request->getMethod(),
            self::FIELD_URI => [
                self::FIELD_URI_SCHEMA => $uri->getScheme(),
                self::FIELD_URI_USER => $userInfo[0] ?? null,
                self::FIELD_URI_PASS => $userInfo[1] ?? null,
                self::FIELD_URI_HOST => $uri->getHost(),
                self::FIELD_URI_PORT => $uri->getPort(),
                self::FIELD_URI_PATH => $uri->getPath(),
                self::FIELD_URI_QUERY => $uri->getQuery(),
                self::FIELD_URI_FRAGMENT => $uri->getFragment(),
            ],
            self::FIELD_HEADERS => $request->getHeaders(),
            self::FIELD_BODY => $request->getBody()->getContents(),
        ];
    }

    /**
     * @inheritDoc
     */
    public function fromArray(array $requestData): RequestInterface
    {
        return new Request(
            $requestData[self::FIELD_METHOD],
            Uri::fromParts($requestData[self::FIELD_URI]),
            $requestData[self::FIELD_HEADERS],
            $requestData[self::FIELD_BODY],
            $requestData[self::FIELD_PROTOCOL_VERSION],
        );
    }
}
