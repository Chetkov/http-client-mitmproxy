<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

class AppMode extends AbstractEnum
{
    private const
        CLI = 'cli',
        WEB = 'web';

    /**
     * @return array<string>
     */
    public static function possibles(): array
    {
        return [
            self::CLI,
            self::WEB,
        ];
    }

    /**
     * @return self
     */
    public static function cli(): self
    {
        return new self(self::CLI);
    }

    /**
     * @return self
     */
    public static function web(): self
    {
        return new self(self::WEB);
    }

    /**
     * @return bool
     */
    public function isCli(): bool
    {
        return $this->value() === self::CLI;
    }

    /**
     * @return bool
     */
    public function isWeb(): bool
    {
        return $this->value() === self::WEB;
    }
}
