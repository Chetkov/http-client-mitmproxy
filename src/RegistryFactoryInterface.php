<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

interface RegistryFactoryInterface
{
    /**
     * @param string $tempDir
     *
     * @return RegistryInterface
     */
    public function create(string $tempDir): RegistryInterface;
}
