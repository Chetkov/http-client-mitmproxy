<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\DataTransform\FormatConverter;

class PHPFormatConverter implements FormatConverterInterface
{
    /**
     * @param string $tempDir
     */
    public function __construct(
        private string $tempDir,
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
        $tmpFilename = $this->tempDir . '/' . md5(uniqid((string) getmypid(), true)) . '.php';
        file_put_contents($tmpFilename, $data);
        try {
            return require $tmpFilename;
        } finally {
            unlink($tmpFilename);
        }
    }
}
