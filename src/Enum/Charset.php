<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

class Charset extends AbstractEnum
{
    private const
        UTF8 = 'UTF-8',
        WINDOWS1251 = 'Windows-1251';

    /**
     * @return array<string>
     */
    public static function possibles(): array
    {
        return [
            self::UTF8,
            self::WINDOWS1251,
        ];
    }

    /**
     * @return self
     */
    public static function utf8(): self
    {
        return new self(self::UTF8);
    }

    /**
     * @return self
     */
    public static function windows1251(): self
    {
        return new self(self::WINDOWS1251);
    }

    /**
     * @return bool
     */
    public function isUtf8(): bool
    {
        return $this->value() === self::UTF8;
    }

    /**
     * @return bool
     */
    public function isWindows1251(): bool
    {
        return $this->value() === self::WINDOWS1251;
    }

    /**
     * @param string $data
     *
     * @return self
     */
    public static function detect(string $data): self
    {
        return self::fromValue(mb_detect_encoding($data, self::possibles(), true));
    }
}
