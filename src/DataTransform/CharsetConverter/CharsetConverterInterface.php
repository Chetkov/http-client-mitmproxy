<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter;

use Chetkov\HttpClientMitmproxy\Enum\Charset;

interface CharsetConverterInterface
{
    /**
     * @param string $data
     * @param Charset $targetCharset
     *
     * @return string
     */
    public function convert(string $data, Charset $targetCharset): string;

    /**
     * @param array $data
     * @param Charset $targetCharset
     * @param array<string, Charset> $sourceCharsets
     *
     * @return array
     */
    public function convertData(array $data, Charset $targetCharset, array &$sourceCharsets = []): array;

    /**
     * @param array $data
     * @param array<string, Charset> $sourceCharsets
     *
     * @return array
     */
    public function reverseData(array $data, array $sourceCharsets): array;
}
