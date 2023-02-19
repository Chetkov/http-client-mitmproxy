<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication\Message;

use Chetkov\HttpClientMitmproxy\Exception\UnexpectedValueException;

/**
 * @method static self create(string $command)
 */
class Command extends AbstractMessage
{
    private const
        COMMAND_SKIP = 'skip',
        COMMAND_EDIT = 'edit';

    private const POSSIBLES = [
        self::COMMAND_SKIP,
        self::COMMAND_EDIT,
    ];

    /**
     * @param string $command
     */
    protected function __construct(string $command)
    {
        if (!in_array($command, self::POSSIBLES, true)) {
            throw new UnexpectedValueException($command, self::POSSIBLES);
        }

        parent::__construct($command);
    }

    /**
     * @return array<string>
     */
    public static function possibles(): array
    {
        return self::POSSIBLES;
    }

    /**
     * @return self
     */
    public static function skip(): self
    {
        return new self(self::COMMAND_SKIP);
    }

    /**
     * @return self
     */
    public static function edit(): self
    {
        return new self(self::COMMAND_EDIT);
    }

    /**
     * @return bool
     */
    public function isSkip(): bool
    {
        return $this->message === self::COMMAND_SKIP;
    }

    /**
     * @return bool
     */
    public function isEdit(): bool
    {
        return $this->message === self::COMMAND_EDIT;
    }
}
