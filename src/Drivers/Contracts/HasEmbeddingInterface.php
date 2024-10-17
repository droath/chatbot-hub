<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

/**
 * Define the embedding interface.
 */
interface HasEmbeddingInterface
{
    /**
     * @return mixed
     */
    public function embeddings();
}
