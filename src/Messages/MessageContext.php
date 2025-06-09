<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Livewire\Wireable;

class MessageContext implements Wireable
{
    /**
     * @param string $content
     * @param array $metadata
     */
    private function __construct(
        public string $content,
        public array $metadata
    ) {}

    /**
     * @param string $content
     * @param array $metadata
     *
     * @return self
     */
    public static function make(
        string $content,
        array $metadata = []
    ): self
    {
        return new self($content, $metadata);
    }

    /**
     * @param $value
     *
     * @return self
     */
    public static function fromLivewire($value): self
    {
        return self::make(
            $value['content'],
            $value['metadata'] ?? []
        );
    }

    /**
     * @param string $key
     * @param $default
     *
     * @return mixed|null
     */
    public function getMetadataValue(
        string $key,
        $default = null
    ): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * @return array
     */
    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
            'metadata' => $this->metadata,
        ];
    }
}
