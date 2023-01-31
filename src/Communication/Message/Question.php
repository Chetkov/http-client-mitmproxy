<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication\Message;

/**
 * @method static create(string $question, array $choices, string $default = '')
 */
class Question extends AbstractMessage
{
    /**
     * @param string $question
     * @param array<string> $choices
     * @param string $default
     */
    protected function __construct(
        string $question,
        private array $choices,
        private string $default = ''
    ) {
        parent::__construct($question);
    }

    /**
     * @return string
     */
    public function getQuestion(): string
    {
        return $this->message;
    }

    /**
     * @return array<string>
     */
    public function getChoices(): array
    {
        return $this->choices;
    }

    /**
     * @return string
     */
    public function getDefault(): string
    {
        return $this->default;
    }

    /**
     * @inheritDoc
     */
    protected function getConstructorArgumentValues(): array
    {
        return [$this->message, $this->choices, $this->default];
    }
}
