<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

class Editor extends AbstractEnum
{
    private const
        NANO = 'nano',
        VIM = 'vim',
        GEDIT = 'gedit';

    /**
     * @return array<string>
     */
    public static function possibles(): array
    {
        return [
            self::NANO,
            self::VIM,
            self::GEDIT,
        ];
    }

    /**
     * @return self
     */
    public static function nano(): self
    {
        return new self(self::NANO);
    }

    /**
     * @return self
     */
    public static function vim(): self
    {
        return new self(self::VIM);
    }

    /**
     * @return self
     */
    public static function gedit(): self
    {
        return new self(self::GEDIT);
    }

    /**
     * @return bool
     */
    public function isNano(): bool
    {
        return $this->value() === self::NANO;
    }

    /**
     * @return bool
     */
    public function isVim(): bool
    {
        return $this->value() === self::VIM;
    }

    /**
     * @return bool
     */
    public function isGedit(): bool
    {
        return $this->value() === self::GEDIT;
    }
}
