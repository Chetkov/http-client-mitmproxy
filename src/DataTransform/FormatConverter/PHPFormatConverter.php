<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

class PHPFormatConverter implements FormatConverterInterface
{
    /**
     * @param string $storageDir
     */
    public function __construct(
        private string $storageDir,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function convert(array $data): string
    {
        return '<?php return ' . var_export($data, true) . ';';
    }

    /**
     * @inheritDoc
     */
    public function reverse(string $data): array
    {
        $tmpFilename = $this->storageDir . '/' . md5(uniqid((string) getmypid(), true)) . '.php';
        file_put_contents($tmpFilename, $data);
        try {
            return require $tmpFilename;
        } finally {
            unlink($tmpFilename);
        }
    }
}
