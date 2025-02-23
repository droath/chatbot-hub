<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Responses;

use Illuminate\Support\Arr;

/**
 * Define the chatbot hub response message.
 */
final readonly class ChatbotHubResponseMessage
{
    private function __construct(
        public ?string $message,
    ) {}

    public static function fromString(string $message): self
    {
        return new self($message);
    }

    public static function fromArray(string $key, array $message): self
    {
        return new self(Arr::get($message, $key));
    }
}
