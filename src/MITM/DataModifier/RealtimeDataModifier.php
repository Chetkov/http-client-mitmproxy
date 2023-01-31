<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM\DataModifier;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\Message\Info;
use Chetkov\HttpClientMitmproxy\Communication\Message\ModifiableData;
use Chetkov\HttpClientMitmproxy\Communication\Message\Question;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestExporterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseExporterInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RealtimeDataModifier implements DataModifierInterface
{
    /**
     * @param CommunicationChannelInterface $channel
     * @param RequestExporterInterface $requestExporter
     * @param ResponseExporterInterface $responseExporter
     */
    public function __construct(
        private CommunicationChannelInterface $channel,
        private RequestExporterInterface $requestExporter,
        private ResponseExporterInterface $responseExporter,
    ) {
        $info = Info::create('***MITM STARTED***');
        $this->channel->sendMessage($info);
    }

    public function __destruct()
    {
        $info = Info::create('***MITM FINISHED***');
        $this->channel->sendMessage($info);
    }

    /**
     * @inheritDoc
     */
    public function modifyRequest(RequestInterface $request): RequestInterface
    {
        $question = Question::create("Отредактировать запрос '{$request->getUri()}' перед отправкой?", ['skip', 'edit'], 'skip');
        $this->channel->sendMessage($question);

        $command = $this->channel->waitMessage()->asCommand();
        if ($command->isSkip()) {
            return $request;
        }

        if ($command->isEdit()) {
            $modifiableData = ModifiableData::create($this->requestExporter->exportRequest($request));
            $this->channel->sendMessage($modifiableData);
        }

        $modifiedRequest = $this->channel->waitMessage()->asModifiableData();

        return $this->requestExporter->importRequest((string) $modifiedRequest);
    }

    /**
     * @inheritDoc
     */
    public function modifyResponse(ResponseInterface $response): ResponseInterface
    {
        $question = Question::create('Отредактировать ответ перед продолжением?', ['skip', 'edit'], 'skip');
        $this->channel->sendMessage($question);

        $command = $this->channel->waitMessage()->asCommand();
        if ($command->isSkip()) {
            return $response;
        }

        if ($command->isEdit()) {
            $modifiableData = ModifiableData::create($this->responseExporter->exportResponse($response));
            $this->channel->sendMessage($modifiableData);
        }

        $modifiedResponse = $this->channel->waitMessage()->asModifiableData();

        return $this->responseExporter->importResponse((string) $modifiedResponse);
    }
}
