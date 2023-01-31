<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

use Chetkov\HttpClientMitmproxy\Exception\UnexpectedValueException;

class Editor
{
    public const
        NANO = 'nano',
        VIM = 'vim',
        GEDIT = 'gedit';

    public const POSSIBLES = [
        self::NANO,
        self::VIM,
        self::GEDIT,
    ];

    /**
     * @param string $editor
     */
    public function __construct(
        private string $editor,
    ) {
        if (!in_array($editor, self::POSSIBLES, true)) {
            throw new UnexpectedValueException($editor, self::POSSIBLES);
        }
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
     * @param string $editor
     *
     * @return self
     */
    public static function fromString(string $editor): self
    {
        return new self($editor);
    }

    /**
     * @return bool
     */
    public function isNano(): bool
    {
        return $this->editor === self::NANO;
    }

    /**
     * @return bool
     */
    public function isVim(): bool
    {
        return $this->editor === self::VIM;
    }

    /**
     * @return bool
     */
    public function isGedit(): bool
    {
        return $this->editor === self::GEDIT;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->editor;
    }
}
