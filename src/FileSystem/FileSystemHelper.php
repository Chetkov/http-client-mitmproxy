<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\FileSystem;

use Chetkov\HttpClientMitmproxy\Exception\FileSystemException;
use FilesystemIterator;

class FileSystemHelper
{
    /**
     * @param string $path
     *
     * @return void
     */
    public function makeDir(string $path): void
    {
        if (!is_dir($path) && !mkdir($path) && !is_dir($path)) {
            throw new FileSystemException(sprintf('Directory "%s" was not created', $path));
        }
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function recursiveRemoveDir(string $path): void
    {
        $includes = new FilesystemIterator($path);
        foreach ($includes as $include) {
            if (is_dir($include) && !is_link($include)) {
                $this->recursiveRemoveDir($include);
            } else {
                unlink($include);
            }
        }

        rmdir($path);
    }
}
