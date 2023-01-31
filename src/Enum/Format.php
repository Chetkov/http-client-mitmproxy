<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

use Chetkov\HttpClientMitmproxy\Exception\UnexpectedValueException;

class Format
{
    public const
        YAML = 'yaml',
        JSON = 'json',
        PHP = 'php';

    public const POSSIBLES = [
        self::YAML,
        self::JSON,
        self::PHP,
    ];

    /**
     * @param string $format
     */
    public function __construct(
        private string $format,
    ) {
        if (!in_array($format, self::POSSIBLES, true)) {
            throw new UnexpectedValueException($this->format, self::POSSIBLES);
        }
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
     * @param string $format
     *
     * @return self
     */
    public static function fromString(string $format): self
    {
        return new self($format);
    }

    /**
     * @return bool
     */
    public function isYaml(): bool
    {
        return $this->format === self::YAML;
    }

    /**
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->format === self::JSON;
    }

    /**
     * @return bool
     */
    public function isPhp(): bool
    {
        return $this->format === self::PHP;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->format;
    }
}
