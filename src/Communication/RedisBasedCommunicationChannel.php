<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication;

use Chetkov\HttpClientMitmproxy\Communication\Message\AbstractMessage;

class RedisBasedCommunicationChannel implements CommunicationChannelInterface
{
    private int $messageCounter;

    /**
     * @param \Redis $redis
     * @param string $proxyUid
     *
     * @throws \RedisException
     */
    public function __construct(
        private \Redis $redis,
        private string $proxyUid,
    ) {
        $this->messageCounter = $this->getLastSharedCounterValue();
    }

    /**
     * @throws \RedisException
     */
    public function __destruct()
    {
        $this->redis->del($this->getMessageCacheKey());
        $this->redis->del($this->getCounterCacheKey());
    }

    /**
     * @inheritDoc
     *
     * @throws \RedisException
     */
    public function sendMessage(AbstractMessage $message): void
    {
        while ($this->messageCounter > $this->getLastReadCounterValue()) {
            // Ждем, пока подписчик прочитает предыдущее сообщение, чтоб не перетереть его новым
            usleep(100000);
        }

        $this->redis->set($this->getMessageCacheKey(), $message->toJson());
        $this->messageCounter = $this->redis->incr($this->getCounterCacheKey());
    }

    /**
     * @inheritDoc
     *
     * @throws \RedisException
     */
    public function waitMessage(int $loopIntervalInMs = 100): AbstractMessage
    {
        $loopIntervalInMicroseconds = $loopIntervalInMs * 1000;

        while (true) {
            $lastCounterValue = $this->getLastSharedCounterValue();
            if ($lastCounterValue !== $this->messageCounter) {
                $this->messageCounter = $lastCounterValue;
                $this->redis->set($this->getLastReadCacheKey(), (string) $lastCounterValue);

                $jsonMessage = $this->redis->get($this->getMessageCacheKey());
                if ($jsonMessage !== false) {
                    return AbstractMessage::fromJson($jsonMessage);
                }
            }
            usleep($loopIntervalInMicroseconds);
        }
    }

    /**
     * @return string
     */
    private function getMessageCacheKey(): string
    {
        return "mitm_communication_channel_$this->proxyUid";
    }

    /**
     * @return string
     */
    private function getCounterCacheKey(): string
    {
        return $this->getMessageCacheKey() . '_counter';
    }

    /**
     * @return string
     */
    private function getLastReadCacheKey(): string
    {
        return $this->getMessageCacheKey() . '_last_read';
    }

    /**
     * @return int
     *
     * @throws \RedisException
     */
    private function getLastSharedCounterValue(): int
    {
        return (int) $this->redis->get($this->getCounterCacheKey()) ?: 0;
    }

    /**
     * @return int
     *
     * @throws \RedisException
     */
    private function getLastReadCounterValue(): int
    {
        return (int) $this->redis->get($this->getLastReadCacheKey()) ?: 0;
    }
}
