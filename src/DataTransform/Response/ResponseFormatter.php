<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\Response;

use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ResponseFormatter implements ResponseFormatterInterface
{
    private const
        FIELD_PROTOCOL_VERSION = 'protocol_version',
        FIELD_STATUS_CODE = 'status_code',
        FIELD_REASON_PHRASE = 'reason_phrase',
        FIELD_HEADERS = 'headers',
        FIELD_BODY = 'body';

    /**
     * @param ResponseInterface $response
     *
     * @return array
     */
    public function toArray(ResponseInterface $response): array
    {
        return [
            self::FIELD_PROTOCOL_VERSION => $response->getProtocolVersion(),
            self::FIELD_STATUS_CODE => $response->getStatusCode(),
            self::FIELD_REASON_PHRASE => $response->getReasonPhrase(),
            self::FIELD_HEADERS => $response->getHeaders(),
            self::FIELD_BODY => $response->getBody()->getContents(),
        ];
    }

    /**
     * @param array $responseData
     *
     * @return ResponseInterface
     */
    public function fromArray(array $responseData): ResponseInterface
    {
        return new Response(
            $responseData[self::FIELD_STATUS_CODE],
            $responseData[self::FIELD_HEADERS],
            $responseData[self::FIELD_BODY],
            $responseData[self::FIELD_PROTOCOL_VERSION],
            $responseData[self::FIELD_REASON_PHRASE],
        );
    }
}
