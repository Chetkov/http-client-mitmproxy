<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

class SymfonyConsoleIOAdapter implements ConsoleIOInterface
{
    /**
     * @param SymfonyStyle $console
     */
    public function __construct(
        private SymfonyStyle $console,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function info(string $message): void
    {
        $this->console->note($message);
    }

    /**
     * @inheritDoc
     */
    public function warning(string $message): void
    {
        $this->console->warning($message);
    }

    /**
     * @inheritDoc
     */
    public function error(string $message): void
    {
        $this->console->error($message);
    }

    /**
     * @inheritDoc
     */
    public function ask(string $question, ?string $default = null): string
    {
        return $this->console->ask($question, $default);
    }

    /**
     * @inheritDoc
     */
    public function choice(string $question, array $choices, ?string $default = null): string
    {
        return (string) $this->console->choice($question, $choices, $default);
    }
}
