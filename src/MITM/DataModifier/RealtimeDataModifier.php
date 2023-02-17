<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM\DataModifier;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\Message\Info;
use Chetkov\HttpClientMitmproxy\Communication\Message\ModifiableData;
use Chetkov\HttpClientMitmproxy\Communication\Message\Question;
use Chetkov\HttpClientMitmproxy\DataTransform\CharsetConverter\CharsetConverterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Request\RequestFormatterInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\Response\ResponseFormatterInterface;
use Chetkov\HttpClientMitmproxy\Enum\Charset;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RealtimeDataModifier implements DataModifierInterface
{
    /**
     * @param CommunicationChannelInterface $channel
     * @param RequestFormatterInterface $requestFormatter
     * @param ResponseFormatterInterface $responseFormatter
     * @param CharsetConverterInterface $charsetConverter
     */
    public function __construct(
        private CommunicationChannelInterface $channel,
        private RequestFormatterInterface $requestFormatter,
        private ResponseFormatterInterface $responseFormatter,
        private CharsetConverterInterface $charsetConverter,
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

        $sourceCharsets = [];
        if ($command->isEdit()) {
            $dataInSourceCharset = $this->requestFormatter->toArray($request);
            $dataInUnicode = $this->charsetConverter->convertData($dataInSourceCharset, Charset::utf8(), $sourceCharsets);

            $modifiableData = ModifiableData::create($dataInUnicode);
            $this->channel->sendMessage($modifiableData);
        }

        $modifiedRequest = $this->channel->waitMessage()->asModifiableData();

        $modifiedDataInUnicode = $modifiedRequest->getData();
        $modifiedDataInSourceCharset = $this->charsetConverter->reverseData($modifiedDataInUnicode, $sourceCharsets);

        return $this->requestFormatter->fromArray($modifiedDataInSourceCharset);
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

        $sourceCharsets = [];
        if ($command->isEdit()) {
            $dataInSourceCharset = $this->responseFormatter->toArray($response);
            $dataInUnicode = $this->charsetConverter->convertData($dataInSourceCharset, Charset::utf8(), $sourceCharsets);

            $modifiableData = ModifiableData::create($dataInUnicode);
            $this->channel->sendMessage($modifiableData);
        }

        $modifiedResponse = $this->channel->waitMessage()->asModifiableData();

        $modifiedDataInUnicode = $modifiedResponse->getData();
        $modifiedDataInSourceCharset = $this->charsetConverter->reverseData($modifiedDataInUnicode, $sourceCharsets);

        return $this->responseFormatter->fromArray($modifiedDataInSourceCharset);
    }
}
