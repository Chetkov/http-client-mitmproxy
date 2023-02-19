<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\Message\Command;
use Chetkov\HttpClientMitmproxy\Communication\Message\Info;
use Chetkov\HttpClientMitmproxy\Communication\Message\ModifiableData;
use Chetkov\HttpClientMitmproxy\Communication\Message\Question;
use Chetkov\HttpClientMitmproxy\Console\ConsoleIOInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterFactory;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterInterface;
use Chetkov\HttpClientMitmproxy\Editor\EditorInterface;
use Chetkov\HttpClientMitmproxy\Enum\Agreement;
use Chetkov\HttpClientMitmproxy\Enum\AppMode;
use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Exception\NotImplementedException;
use Chetkov\HttpClientMitmproxy\Exception\PublishersNotFoundException;
use Chetkov\HttpClientMitmproxy\Helper\ArrayHelper;

class ProxyClient
{
    private FormatConverterInterface $formatConverter;

    /**
     * @param ConsoleIOInterface $io
     * @param FormatConverterFactory $formatConverterFactory
     * @param CommunicationChannelInterface $communicationChannel
     * @param EditorInterface $editor
     * @param ArrayHelper $arrayHelper
     * @param ProxyUID $proxyUid
     */
    public function __construct(
        private ConsoleIOInterface $io,
        private FormatConverterFactory $formatConverterFactory,
        private CommunicationChannelInterface $communicationChannel,
        private EditorInterface $editor,
        private ArrayHelper $arrayHelper,
        private ProxyUID $proxyUid,
    ) {
    }

    /**
     * @param AppMode|null $appMode
     * @param Format|null $format
     * @param Editor|null $editor
     *
     * @return void
     */
    public function start(
        ?AppMode $appMode = null,
        ?Format $format = null,
        ?Editor $editor = null,
    ): void {
        $appMode ??= AppMode::fromValue($this->io->choice('Enter listening application mode:', AppMode::possibles(), (string) AppMode::cli()));
        $format ??= Format::fromValue($this->io->choice('Enter format:', Format::possibles([(string) Format::text()]), (string) Format::yaml()));
        $editor ??= Editor::fromValue($this->io->choice('Enter editor name:', Editor::possibles(), (string) Editor::nano()));

        $this->showInstructions($appMode);

        $this->formatConverter = $this->formatConverterFactory->create($format);

        while (true) {
            try {
                $message = $this->communicationChannel->waitMessage();
                switch (true) {
                    case $message->isInfo():
                        $this->handleInfo($message->asInfo());
                        break;

                    case $message->isQuestion():
                        $this->handleQuestion($message->asQuestion());
                        break;

                    case $message->isModifiableData():
                        $this->handleModifiableData($message->asModifiableData(), $format, $editor);
                        break;

                    default:
                }
            } catch (PublishersNotFoundException) {
            } catch (\Throwable $e) {
                $this->io->error((string) $e);
            }
        }
    }

    public function stop(): void
    {
        // Implement when needed
    }

    /**
     * @param AppMode $appMode
     *
     * @return void
     */
    private function showInstructions(AppMode $appMode): void
    {
        $uidVariableName = ProxyUID::VAR_NAME;
        $info = "ProxyUID: $this->proxyUid" . PHP_EOL . PHP_EOL;
        $info .= match (true) {
            $appMode->isCli() => "Set values to env variables:\n\n`export $uidVariableName=$this->proxyUid && YOUR_COMMAND`",
            $appMode->isWeb() => "Add values to get-parameters:\n\n`YOUR_URL?$uidVariableName=$this->proxyUid`",
            default => throw new NotImplementedException(),
        };
        $this->io->warning($info);
    }

    /**
     * @param Info $info
     *
     * @return void
     */
    private function handleInfo(Info $info): void
    {
        $this->io->info((string) $info);
    }

    /**
     * @param Question $question
     *
     * @return void
     */
    private function handleQuestion(Question $question): void
    {
        $command = Command::create($this->io->choice(
            $question->getQuestion(),
            $question->getChoices(),
            $question->getDefault()
        ));
        $this->communicationChannel->sendMessage($command);
    }

    /**
     * @param ModifiableData $modifiableData
     * @param Format $format
     * @param Editor $editor
     *
     * @return void
     */
    private function handleModifiableData(ModifiableData $modifiableData, Format $format, Editor $editor): void
    {
        $dataForEditing = $modifiableData->getData();
        $elementsPaths = $this->arrayHelper->getElementsPaths($dataForEditing);

        while (true) {
            $partialEditingAgreement = Agreement::fromValue($this->io->choice(
                question: 'Would you like to open concrete element in editor to modify?',
                choices: Agreement::possibles(),
                default: (string) Agreement::no()
            ));

            if ($partialEditingAgreement->isNo()) {
                break;
            }

            $elementPath = $this->io->choice('Enter element path:', $elementsPaths);
            $elementValue = $this->arrayHelper->getElementValue($dataForEditing, $elementPath);

            $elementValue = $this->editor->edit((string) $elementValue, Format::text(), $editor);

            $dataForEditing = $this->arrayHelper->setElementValue($dataForEditing, $elementPath, $elementValue);
        }

        $fullEditingAgreement = Agreement::fromValue($this->io->choice(
            question: 'Would you like to edit all data or open it for viewing?',
            choices: Agreement::possibles(),
            default: (string) Agreement::no(),
        ));

        if ($fullEditingAgreement->isYes()) {
            $formattedData = $this->formatConverter->convert($dataForEditing);

            $formattedData = $this->editor->edit($formattedData, $format, $editor);

            $dataForEditing = $this->formatConverter->reverse($formattedData);
        }

        $modifiedData = ModifiableData::create($dataForEditing);
        $this->communicationChannel->sendMessage($modifiedData);
    }
}
