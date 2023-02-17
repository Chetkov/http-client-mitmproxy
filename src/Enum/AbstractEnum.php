<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Enum;

use Chetkov\HttpClientMitmproxy\Exception\UnexpectedValueException;

abstract class AbstractEnum
{
    /**
     * @param float|int|string $value
     */
    public function __construct(
        private float|int|string $value,
    ) {
        if (!in_array($value, static::possibles(), true)) {
            throw new UnexpectedValueException($this->value, self::possibles());
        }
    }

    /**
     * @return array
     */
    abstract public static function possibles(): array;

    /**
     * @param string|int|float $value
     *
     * @return static
     */
    public static function fromValue(string|int|float $value): static
    {
        return new static($value);
    }

    /**
     * @param self $value
     *
     * @return bool
     */
    public function isA(self $value): bool
    {
        return $this->value === $value->value;
    }

    /**
     * @return string|int|float
     */
    public function value(): string|int|float
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return (string) $this->value;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
