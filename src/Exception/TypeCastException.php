<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Exception;

class TypeCastException extends HttpClientMITMException
{
    /**
     * @param string $sourceType
     * @param string $targetType
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $sourceType, string $targetType, int $code = 0, ?\Throwable $previous = null)
    {
        $message = sprintf('Can not cast type %s to %s', $sourceType, $targetType);

        parent::__construct($message, $code, $previous);
    }
}
