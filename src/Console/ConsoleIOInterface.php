<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Console;

interface ConsoleIOInterface
{
    /**
     * @param string $message
     *
     * @return void
     */
    public function info(string $message): void;

    /**
     * @param string $message
     *
     * @return void
     */
    public function warning(string $message): void;

    /**
     * @param string $message
     *
     * @return void
     */
    public function error(string $message): void;

    /**
     * @param string $question
     * @param string|null $default
     *
     * @return string
     */
    public function ask(string $question, ?string $default = null): string;

    /**
     * @param string $question
     * @param array $choices
     * @param string|null $default
     *
     * @return string
     */
    public function choice(string $question, array $choices, ?string $default = null): string;
}
