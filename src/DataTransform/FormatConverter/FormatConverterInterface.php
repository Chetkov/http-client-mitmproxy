<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

interface FormatConverterInterface
{
    /**
     * @param array $data
     *
     * @return string
     */
    public function convert(array $data): string;

    /**
     * @param string $data
     *
     * @return array
     */
    public function reverse(string $data): array;
}
