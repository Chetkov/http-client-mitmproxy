<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication;

use Chetkov\HttpClientMitmproxy\Communication\Message\AbstractMessage;

interface CommunicationChannelInterface
{
    /**
     * @param AbstractMessage $message
     *
     * @return void
     */
    public function sendMessage(AbstractMessage $message): void;

    /**
     * @param int $loopIntervalInMs 1s = 1000ms
     *
     * @return AbstractMessage
     */
    public function waitMessage(int $loopIntervalInMs = 100): AbstractMessage;
}
