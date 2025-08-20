<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Livewire\Wireable;

class MessageContext implements Wireable
{
    private function __construct(
        public string $content,
        public array $metadata
    ) {}

    public static function make(
        string $content,
        array $metadata = []
    ): self {
        return new self($content, $metadata);
    }

    public static function fromLivewire($value): self
    {
        return self::make(
            $value['content'],
            $value['metadata'] ?? []
        );
    }

    /**
     * @return mixed|null
     */
    public function getMetadataValue(
        string $key,
        $default = null
    ): mixed {
        return $this->metadata[$key] ?? $default;
    }

    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
            'metadata' => $this->metadata,
        ];
    }
}
