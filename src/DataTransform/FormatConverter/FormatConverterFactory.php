<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Exception\NotImplementedException;

class FormatConverterFactory
{
    /**
     * @param string $tempDir
     */
    public function __construct(
        private string $tempDir,
    ) {
    }

    /**
     * @param Format $format
     *
     * @return FormatConverterInterface
     */
    public function create(Format $format): FormatConverterInterface
    {
        return match (true) {
            $format->isYaml() => new YAMLFormatConverter(),
            $format->isJson() => new JSONFormatConverter(),
            $format->isPhp() => new PHPFormatConverter($this->tempDir),
            default => throw new NotImplementedException(),
        };
    }
}
