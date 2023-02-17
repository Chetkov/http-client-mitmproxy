<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Editor;

use Chetkov\HttpClientMitmproxy\Enum\Editor;
use Chetkov\HttpClientMitmproxy\Enum\Format;

interface EditorInterface
{
    /**
     * @param string $data
     * @param Format $format
     * @param Editor $editor
     *
     * @return string
     */
    public function edit(string $data, Format $format, Editor $editor): string;
}
