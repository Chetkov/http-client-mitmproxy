<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\MITM;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\Message\Command;
use Chetkov\HttpClientMitmproxy\Communication\Message\ModifiableData;
use Chetkov\HttpClientMitmproxy\Console\ConsoleIOInterface;
use Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter\FormatConverterFactory;
use Chetkov\HttpClientMitmproxy\Editor\EditorInterface;
use Chetkov\HttpClientMitmproxy\Enum\Agreement;
use Chetkov\HttpClientMitmproxy\Enum\AppMode;
use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Exception\NotImplementedException;
use Chetkov\HttpClientMitmproxy\Helper\ArrayHelper;

class ProxyClient
{
    /**
     * @param ConsoleIOInterface $io
     * @param FormatConverterFactory $formatConverterFactory
     * @param CommunicationChannelInterface $communicationChannel
     * @param EditorInterface $editor
     * @param ArrayHelper $arrayHelper
     * @param string $proxyUid
     */
    public function __construct(
        private ConsoleIOInterface $io,
        private FormatConverterFactory $formatConverterFactory,
        private CommunicationChannelInterface $communicationChannel,
        private EditorInterface $editor,
        private ArrayHelper $arrayHelper,
        private string $proxyUid,
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

        $formatConverter = $this->formatConverterFactory->create($format);

        while (true) {
            try {
                $message = $this->communicationChannel->waitMessage();
                if ($message->isInfo()) {
                    $this->io->info((string) $message);
                    continue;
                }

                if ($message->isQuestion() && $question = $message->asQuestion()) {
                    $command = Command::create($this->io->choice($question->getQuestion(), $question->getChoices(), $question->getDefault()));
                    $this->communicationChannel->sendMessage($command);

                    if ($command->isSkip()) {
                        continue;
                    }

                    if ($command->isEdit()) {
                        $modifiableData = $this->communicationChannel->waitMessage()->asModifiableData();

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
                            $formattedData = $formatConverter->convert($dataForEditing);

                            $formattedData = $this->editor->edit($formattedData, $format, $editor);

                            $dataForEditing = $formatConverter->reverse($formattedData);
                        }

                        $modifiedData = ModifiableData::create($dataForEditing);
                        $this->communicationChannel->sendMessage($modifiedData);
                    }
                }
            } catch (\Throwable $e) {
                $this->io->error((string) $e);
            }
        }
    }

    /**
     * @param AppMode $appMode
     *
     * @return void
     */
    private function showInstructions(AppMode $appMode): void
    {
        $info = "ProxyUID: $this->proxyUid" . PHP_EOL . PHP_EOL;
        $info .= match (true) {
            $appMode->isCli() => "Set values to env variables:\n\n`export MITM_PROXY_UID=$this->proxyUid && YOUR_COMMAND`",
            $appMode->isWeb() => "Add values to get-parameters:\n\n`YOUR_URL?MITM_PROXY_UID=$this->proxyUid`",
            default => throw new NotImplementedException(),
        };
        $this->io->warning($info);
    }
}
