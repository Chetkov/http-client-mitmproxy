<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM;

class ProxyUID
{
    public const VAR_NAME = 'MITM_PROXY_UID';

    /**
     * @param string $uid
     */
    public function __construct(
        private string $uid
    ) {
    }

    /**
     * @return ProxyUID
     */
    public static function create(): self
    {
        return new self(md5(uniqid((string) getmypid(), true)));
    }

    /**
     * @return static|null
     */
    public static function detect(): ?self
    {
        $uid = $_GET[self::VAR_NAME]
            ?? $_POST[self::VAR_NAME]
            ?? getenv(self::VAR_NAME);

        return $uid ? new self($uid) : null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->uid;
    }
}
