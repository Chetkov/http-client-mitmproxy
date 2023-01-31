<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

use Chetkov\HttpClientMitmproxy\Communication\CommunicationChannelInterface;
use Chetkov\HttpClientMitmproxy\Communication\Message\Command;
use Chetkov\HttpClientMitmproxy\Communication\Message\ModifiableData;
use Chetkov\HttpClientMitmproxy\Console\ConsoleIOInterface;
use Chetkov\HttpClientMitmproxy\Enum\AppMode;
use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Exception\NotImplementedException;
use Chetkov\HttpClientMitmproxy\FileSystem\FileSystemHelper;

class MITMProxy
{
    private string $proxyUid;
    private string $storageDir;

    /**
     * @param FileSystemHelper $filesystem
     * @param ConsoleIOInterface $io
     * @param CommunicationChannelInterface $communicationChannel
     * @param string $proxyUid
     */
    public function __construct(
        private FileSystemHelper $filesystem,
        private ConsoleIOInterface $io,
        private CommunicationChannelInterface $communicationChannel,
        string $proxyUid,
    ) {
        $this->proxyUid = $proxyUid;
        $this->storageDir = MITM_STORAGE_DIR . '/' . $proxyUid;
        $this->filesystem->makeDir($this->storageDir);
    }

    public function __destruct()
    {
        $this->filesystem->recursiveRemoveDir($this->storageDir);
    }

    public function start(): void
    {
        try {
            $appMode = AppMode::fromString($this->io->choice('Enter listening application mode:', AppMode::POSSIBLES, AppMode::CLI));
            $format = Format::fromString($this->io->choice('Enter format:', Format::POSSIBLES, Format::YAML));
            $editor = Editor::fromString($this->io->choice('Enter editor name:', Editor::POSSIBLES, Editor::NANO));

            $editorPath = $this->getEditorPath($format);

            $info = "ProxyUID: $this->proxyUid." . PHP_EOL . PHP_EOL;
            $info .= match (true) {
                $appMode->isCli() => "Set values to env variables:\n\n`export MITM_PROXY_UID=$this->proxyUid MITM_PROXY_FORMAT=$format && YOUR_COMMAND`",
                $appMode->isWeb() => "Add values to get-parameters:\n\n`YOUR_URL?MITM_PROXY_UID=$this->proxyUid&MITM_PROXY_FORMAT=$format`",
                default => throw new NotImplementedException(),
            };
            $this->io->warning($info);

            while (true) {
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
                        $dataForEditing = $this->communicationChannel->waitMessage();

                        file_put_contents($editorPath, (string) $dataForEditing);
                        system("$editor $editorPath > `tty`");

                        $modifiedData = ModifiableData::create(file_get_contents($editorPath));
                        $this->communicationChannel->sendMessage($modifiedData);
                    }
                }
            }
        } finally {
            $this->filesystem->recursiveRemoveDir($this->storageDir);
        }
    }

    /**
     * @param Format $format
     *
     * @return string
     */
    protected function getEditorPath(Format $format): string
    {
        return $this->storageDir . "/editor.$format";
    }
}
