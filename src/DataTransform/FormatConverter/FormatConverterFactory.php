<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\RegistryInterface;

class FormatConverterFactory
{
    /**
     * @param RegistryInterface $registry
     */
    public function __construct(
        private RegistryInterface $registry,
    ) {
    }

    /**
     * @param Format $format
     *
     * @return FormatConverterInterface
     */
    public function create(Format $format): FormatConverterInterface
    {
        return $this->registry->getFormatConverter($format);
    }
}
