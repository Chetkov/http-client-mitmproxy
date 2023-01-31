<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

use Chetkov\HttpClientMitmproxy\Exception\UnexpectedValueException;

class AppMode
{
    public const
        CLI = 'cli',
        WEB = 'web';

    public const POSSIBLES = [
        self::CLI,
        self::WEB,
    ];

    /**
     * @param string $appMode
     */
    public function __construct(
        private string $appMode,
    ) {
        if (!in_array($appMode, self::POSSIBLES, true)) {
            throw new UnexpectedValueException($this->appMode, self::POSSIBLES);
        }
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
     * @param string $appMode
     *
     * @return self
     */
    public static function fromString(string $appMode): self
    {
        return new self($appMode);
    }

    /**
     * @return bool
     */
    public function isCli(): bool
    {
        return $this->appMode === self::CLI;
    }

    /**
     * @return bool
     */
    public function isWeb(): bool
    {
        return $this->appMode === self::WEB;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->appMode;
    }
}
