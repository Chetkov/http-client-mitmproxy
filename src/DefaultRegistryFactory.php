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
            host: $redisConfig['host'] ?? 'localhost',
            port: $redisConfig['port'] ?? 6379,
            timeout: $redisConfig['timeout'] ?? 0,
        );
    }

    /**
     * @param string $tempDir
     *
     * @return DefaultRegistry
     */
    public function create(string $tempDir): DefaultRegistry
    {
        return new DefaultRegistry($this->redis, $tempDir);
    }
}
