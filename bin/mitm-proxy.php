<?php

declare(strict_types=1);

use Chetkov\HttpClientMitmproxy\Communication\Message\Command;
use Chetkov\HttpClientMitmproxy\Communication\Message\ModifiableData;
use Chetkov\HttpClientMitmproxy\DefaultRegistry;
use Chetkov\HttpClientMitmproxy\Enum\AppMode;
use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\FileSystem\FileSystemHelper;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$filesystem = new FileSystemHelper();

$proxyUid = md5(uniqid((string) getmypid(), true));
$storageDir = dirname(__DIR__) . '/storage/' . $proxyUid;

$filesystem->makeDir($storageDir);

$redis = new Redis();
$redis->connect('localhost');
$registry = new DefaultRegistry($redis);

$communicationChannel = $registry->getCommunicationChannel($proxyUid);

$console = $registry->getConsoleIO();

try {
    $appMode = AppMode::fromString($console->choice('Enter listening application mode:', AppMode::POSSIBLES, AppMode::CLI));
    $format = Format::fromString($console->choice('Enter format:', Format::POSSIBLES, Format::YAML));
    $editor = Editor::fromString($console->choice('Enter editor name:', Editor::POSSIBLES, Editor::NANO));

    $editorPath = $storageDir . "/editor.$format";

    $info = "ProxyUID: $proxyUid." . PHP_EOL . PHP_EOL;
    $info .= match (true) {
        $appMode->isCli() => "Set values to env variables:\n\n`export MITM_PROXY_UID=$proxyUid MITM_PROXY_FORMAT=$format && YOUR_COMMAND`",
        $appMode->isWeb() => "Add values to get-parameters:\n\n`YOUR_URL?MITM_PROXY_UID=$proxyUid&MITM_PROXY_FORMAT=$format`",
        default => throw new \RuntimeException(),
    };
    $console->warning($info);

    while (true) {
        $message = $communicationChannel->waitMessage();
        if ($message->isInfo()) {
            $console->info((string) $message);
            continue;
        }

        if ($message->isQuestion() && $question = $message->asQuestion()) {
            $command = Command::create($console->choice($question->getQuestion(), $question->getChoices(), $question->getDefault()));
            $communicationChannel->sendMessage($command);

            if ($command->isSkip()) {
                continue;
            }

            if ($command->isEdit()) {
                $dataForEditing = $communicationChannel->waitMessage();

                file_put_contents($editorPath, (string) $dataForEditing);
                system("$editor $editorPath > `tty`");

                $modifiedData = ModifiableData::create(file_get_contents($editorPath));
                $communicationChannel->sendMessage($modifiedData);
            }
        }
    }
} finally {
    $filesystem->recursiveRemoveDir($storageDir);
}