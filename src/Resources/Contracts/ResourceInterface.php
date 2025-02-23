<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

interface ResourceInterface
{
    /**
     * Set the resource model.
     *
     * @param string $model
     *   The resource model to use.
     */
    public function withModel(string $model): static;
}
