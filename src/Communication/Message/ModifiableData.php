<?php

declare(strict_types=1);

namespace Chetkov\HttpClientMitmproxy\Communication\Message;

/**
 * @method static self create(array $data) request or response data (array or serialized array)
 */
class ModifiableData extends AbstractMessage
{
    /**
     * @param string|array<string, mixed> $data
     */
    protected function __construct(string|array $data)
    {
        $data = is_array($data) ? serialize($data) : $data;

        parent::__construct($data);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return unserialize($this->message, ['allowed_classes' => true]);
    }
}
