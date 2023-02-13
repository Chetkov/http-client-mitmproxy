<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

class DefaultRegistryFactory implements RegistryFactoryInterface
{
    private \Redis $redis;

    /**
     * @param array{
     *     host: string,
     *     port: int,
     *     timeout: float,
     *     read_timeout: float,
     *     retry_interval: int
     * } $redisConfig
     *
     * @throws \RedisException
     */
    public function __construct(array $redisConfig = [])
    {
        $this->redis = new \Redis();
        $this->redis->connect(
            host: $redisConfig['host'],
            port: $redisConfig['port'],
            timeout: $redisConfig['timeout'],
            retry_interval: $redisConfig['retry_interval'],
            read_timeout: $redisConfig['read_timeout'],
        );
    }

    /**
     * @param string $storageDir
     *
     * @return DefaultRegistry
     */
    public function create(string $storageDir): DefaultRegistry
    {
        return new DefaultRegistry($this->redis, $storageDir);
    }
}
