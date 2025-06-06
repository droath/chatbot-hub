<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Livewire\Wireable;
use Illuminate\Contracts\Support\Arrayable;

abstract class MessageBase implements Wireable, Arrayable
{
    /**
     * @param string $content
     */
    private function __construct(
        public readonly string $content,
    ) {}

    /**
     * @param $value
     *
     * @return self
     */
    public static function fromLivewire($value): self
    {
        return self::make(
            $value['content'],
        );
    }

    /**
     * @param array $value
     *
     * @return self
     */
    public static function fromValue(array $value): mixed
    {
        return static::make(
            $value['content'],
        );
    }

    /**
     * @param string $content
     *
     * @return static
     */
    public static function make(
        string $content,
    ): mixed
    {
        return new static($content);
    }

    /**
     * @return array
     */
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
