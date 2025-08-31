<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Responses;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

    /**
     * @throws \JsonException
     */
    public function toArray(): array
    {
        if (Str::isJson($this->message)) {
            return json_decode(
                $this->message,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        return [];
    }

    public function __toString(): string
    {
        return $this->message ?? '';

    }
}
