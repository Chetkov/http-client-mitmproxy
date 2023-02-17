<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

class Format extends AbstractEnum
{
    private const
        YAML = 'yaml',
        JSON = 'json',
        PHP = 'php',
        TEXT = 'txt';

    /**
     * @return array<string>
     */
    public static function possibles(array $filter = []): array
    {
        return array_diff([
            self::YAML,
            self::JSON,
            self::PHP,
            self::TEXT,
        ], $filter);
    }

    /**
     * @return self
     */
    public static function yaml(): self
    {
        return new self(self::YAML);
    }

    /**
     * @return self
     */
    public static function json(): self
    {
        return new self(self::JSON);
    }

    /**
     * @return self
     */
    public static function php(): self
    {
        return new self(self::PHP);
    }

    /**
     * @return self
     */
    public static function text(): self
    {
        return new self(self::TEXT);
    }

    /**
     * @return bool
     */
    public function isYaml(): bool
    {
        return $this->value() === self::YAML;
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->value() === self::JSON;
    }

    /**
     * @return bool
     */
    public function isPhp(): bool
    {
        return $this->value() === self::PHP;
    }

    /**
     * @return bool
     */
    public function isText(): bool
    {
        return $this->value() === self::TEXT;
    }
}
