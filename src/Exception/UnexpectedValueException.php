<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Exception;

class UnexpectedValueException extends InvalidArgumentException
{
    /**
     * @param string $value
     * @param array<string> $possibles
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $value, array $possibles, int $code = 0, ?\Throwable $previous = null)
    {
        $message = sprintf('Unexpected value %s, possible values: %s', $value, implode(', ', $possibles));

        parent::__construct($message, $code, $previous);
    }
}
