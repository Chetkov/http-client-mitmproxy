<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication\Message;

use Chetkov\HttpClientMitmproxy\Exception\TypeCastException;

abstract class AbstractMessage
{
    /**
     * @param string $message
     */
    protected function __construct(
        protected string $message,
    ) {
    }

    /**
     * @param mixed ...$arguments
     *
     * @return static
     */
    public static function create(...$arguments): static
    {
        return new static(...$arguments);
    }

    /**
     * @return bool
     */
    public function isInfo(): bool
    {
        return $this instanceof Info;
    }

    /**
     * @return Info
     */
    public function asInfo(): Info
    {
        if (!$this instanceof Info) {
            throw new TypeCastException(static::class, Info::class);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isQuestion(): bool
    {
        return $this instanceof Question;
    }

    /**
     * @return Question
     */
    public function asQuestion(): Question
    {
        if (!$this instanceof Question) {
            throw new TypeCastException(static::class, Question::class);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isCommand(): bool
    {
        return $this instanceof Command;
    }

    /**
     * @return Command
     */
    public function asCommand(): Command
    {
        if (!$this instanceof Command) {
            throw new TypeCastException(static::class, Command::class);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isModifiableData(): bool
    {
        return $this instanceof ModifiableData;
    }

    /**
     * @return ModifiableData
     */
    public function asModifiableData(): ModifiableData
    {
        if (!$this instanceof ModifiableData) {
            throw new TypeCastException(static::class, ModifiableData::class);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function toJson(): string
    {
        try {
            return json_encode([
                'class' => get_class($this),
                'arguments' => $this->getConstructorArgumentValues(),
            ], JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new TypeCastException('array', 'json', previous: $e);
        }
    }

    /**
     * @param string $encoded
     *
     * @return static
     */
    public static function fromJson(string $encoded): static
    {
        try {
            ['class' => $class, 'arguments' => $arguments] = json_decode($encoded, true, 512, JSON_THROW_ON_ERROR);

            return new $class(...$arguments);
        } catch (\JsonException $e) {
            throw new TypeCastException('json', 'array', previous: $e);
        }
    }

    /**
     * @return array<string>
     */
    protected function getConstructorArgumentValues(): array
    {
        return [$this->message];
    }
}
