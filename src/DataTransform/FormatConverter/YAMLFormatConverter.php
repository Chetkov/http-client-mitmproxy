<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

class YAMLFormatConverter implements FormatConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(array $data): string
    {
        return yaml_emit($data);
    }

    /**
     * @inheritDoc
     */
    public function reverse(string $data): array
    {
        return yaml_parse($data);
    }
}
