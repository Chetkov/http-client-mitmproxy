<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\Response;

use Psr\Http\Message\ResponseInterface;

interface ResponseFormatterInterface
{
    public function toArray(ResponseInterface $response): array;

    public function fromArray(array $responseData): ResponseInterface;
}
