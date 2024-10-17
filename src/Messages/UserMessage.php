<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Define the user message value object.
 */
final readonly class UserMessage implements Arrayable
{
    private function __construct(
        public string $content,
    )
    {
    }

    public static function make(string $content): self
    {
        return new self($content);
    }

    public function toArray(): array
    {
        return [
            'role' => ChatbotRoles::USER->value,
            'content' => $this->content,
        ];
    }
}
