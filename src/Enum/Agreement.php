<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

class Agreement extends AbstractEnum
{
    private const
        YES = 'yes',
        NO = 'no';

    /**
     * @return array<string>
     */
    public static function possibles(): array
    {
        return [
            self::NO,
            self::YES,
        ];
    }

    /**
     * @return self
     */
    public static function yes(): self
    {
        return new self(self::YES);
    }

    /**
     * @return self
     */
    public static function no(): self
    {
        return new self(self::NO);
    }

    /**
     * @return bool
     */
    public function isYes(): bool
    {
        return $this->value() === self::YES;
    }

    /**
     * @return bool
     */
    public function isNo(): bool
    {
        return $this->value() === self::NO;
    }
}
