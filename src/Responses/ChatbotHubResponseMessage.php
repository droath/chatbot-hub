<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Responses;

use OpenAI\Responses\Chat\CreateResponseChoice;

/**
 * Define the chatbot hub response message.
 */
final readonly class ChatbotHubResponseMessage
{
    private function __construct(
        public ?string $message,
    )
    {
    }

    public static function fromString(string $message): self
    {
        return new self($message);
    }

    public static function from(CreateResponseChoice $response): self
    {
        return new self($response->message->content);
    }
}
