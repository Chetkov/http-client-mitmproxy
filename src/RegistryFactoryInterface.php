<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy;

interface RegistryFactoryInterface
{
    /**
     * @param string $storageDir
     *
     * @return RegistryInterface
     */
    public function create(string $storageDir): RegistryInterface;
}
