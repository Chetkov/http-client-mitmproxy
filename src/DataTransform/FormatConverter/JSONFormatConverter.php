<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

class JSONFormatConverter implements FormatConverterInterface
{
    /**
     * @inheritDoc
     *
     * @throws \JsonException
     */
    public function convert(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR, JSON_PRETTY_PRINT);
    }

    /**
     * @inheritDoc
     *
     * @throws \JsonException
     */
    public function reverse(string $data): array
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }
}
