<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Illuminate\Contracts\Support\Arrayable;
use Livewire\Wireable;

abstract class MessageBase implements Arrayable, Wireable
{
    private function __construct(
        public readonly string $content,
    ) {}

    public static function fromLivewire($value): self
    {
        return self::make(
            $value['content'],
        );
    }

    /**
     * @return self
     */
    public static function fromValue(array $value): mixed
    {
        return static::make(
            $value['content'],
        );
    }

    /**
     * @return static
     */
    public static function make(
        string $content,
    ): mixed {
        return new static($content);
    }

    public function toValue(): array
    {
        return $this->toArray();
    }

    /**
     * @return string[]
     */
    public function toLivewire(): array
    {
        return [
            'content' => $this->content,
        ];
    }
}
