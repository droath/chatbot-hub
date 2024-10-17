<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Messages;

use Droath\ChatbotHub\Enums\ChatbotRoles;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Define the system message value object.
 */
final readonly class SystemMessage implements Arrayable
{
    private function __construct(
        readonly string $content,
    ) {}

    public static function make(string $content): self
    {
        return new self($content);
    }

    public function toArray(): array
    {
        return [
            'role' => ChatbotRoles::SYSTEM->value,
            'content' => $this->content,
        ];
    }
}
