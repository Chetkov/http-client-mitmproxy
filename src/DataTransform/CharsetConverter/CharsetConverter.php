<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter;

use Chetkov\HttpClientMitmproxy\Enum\Charset;

class CharsetConverter implements CharsetConverterInterface
{
    /**
     * @inheritDoc
     */
    public function convert(string $data, Charset $targetCharset): string
    {
        $sourceCharset = Charset::detect($data);

        return $sourceCharset->isA($targetCharset) ? $data : iconv($sourceCharset->toString(), $targetCharset->toString(), $data);
    }

    /**
     * @inheritDoc
     */
    public function convertData(array $data, Charset $targetCharset, array &$sourceCharsets = [], string $rootPath = ''): array
    {
        foreach ($data as $key => $element) {
            $path = "$rootPath.$key";
            if (is_array($element)) {
                $data[$key] = $this->convertData($element, $targetCharset, $sourceCharsets, $path);
            } elseif (is_string($element)) {
                $sourceCharsets[$path] = Charset::detect($element);
                $data[$key] = $this->convert($element, $targetCharset);
            }
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function reverseData(array $data, array $sourceCharsets, string $rootPath = ''): array
    {
        foreach ($data as $key => $element) {
            $path = "$rootPath.$key";
            if (is_array($element)) {
                $data[$key] = $this->reverseData($element, $sourceCharsets, $path);
            } elseif (is_string($element) && isset($sourceCharsets[$path])) {
                $data[$key] = $this->convert($element, $sourceCharsets[$path]);
            }
        }

        return $data;
    }
}
