<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication;

use Chetkov\HttpClientMitmproxy\Communication\Message\AbstractMessage;
use Chetkov\HttpClientMitmproxy\MITM\ProxyUID;

class RedisPubSubBasedCommunicationChannel implements CommunicationChannelInterface
{
    /**
     * @param \Redis $redis
     * @param ProxyUID $proxyUid
     */
    public function __construct(
        private \Redis $redis,
        private ProxyUID $proxyUid,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @throws \RedisException
     */
    public function sendMessage(AbstractMessage $message): void
    {
        $this->redis->publish($this->getChannel(), $message->toJson());
    }

    /**
     * @inheritDoc
     */
    public function waitMessage(int $loopIntervalInMs = 100): AbstractMessage
    {
        $message = null;
        $loopIntervalInMicroseconds = $loopIntervalInMs * 1000;

        while (true) {
            try {
                $channels = [$this->getChannel()];
                $callback = static function (\Redis $redis, string $channel, string $receivedMessage) use (&$message) {
                    $message = AbstractMessage::fromJson($receivedMessage);
                    $redis->close();
                };
                $this->redis->subscribe($channels, $callback);
            } catch (\RedisException) {
            }

            if ($message !== null) {
                break;
            }

            usleep($loopIntervalInMicroseconds);
        }

        return $message;
    }

    /**
     * @return string
     */
    private function getChannel(): string
    {
        return "mitm_communication_channel_$this->proxyUid";
    }
}
