<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Responses;

/**
 * Define the chatbot hub response embeddings.
 */
final readonly class ChatbotHubResponseEmbeddings
{
    private function __construct(
        public array $embeddings,
    ) {}

    /**
     * @param array $embeddings
     *
     * @return self
     */
    public static function fromArray(array $embeddings): self
    {
        return new self($embeddings);
    }
}
