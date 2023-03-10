<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Editor;

use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;
use Chetkov\HttpClientMitmproxy\Helper\FileSystemHelper;

class FileBasedEditor implements EditorInterface
{
    /**
     * @param FileSystemHelper $filesystem
     * @param string $tempDir
     */
    public function __construct(
        private FileSystemHelper $filesystem,
        private string $tempDir,
    ) {
        $this->filesystem->makeDir($this->tempDir);
    }

    public function __destruct()
    {
        $this->filesystem->recursiveRemoveDir($this->tempDir);
    }

    /**
     * @inheritDoc
     */
    public function edit(string $data, Format $format, Editor $editor): string
    {
        $tmpFilePath = $this->tempDir . "/editor.$format";
        file_put_contents($tmpFilePath, $data);
        system("$editor $tmpFilePath > `tty`");
        return file_get_contents($tmpFilePath);
    }
}
