<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

use Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface;

/**
 * Define the embedding interface.
 */
interface HasEmbeddingInterface
{
    /**
     * Define the embeddings resource.
     */
    public function embeddings(): EmbeddingsResourceInterface;
}
