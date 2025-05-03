<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Concerns;

trait WithModel
{
    /**
     * {@inheritDoc}
     */
    public function withModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }
}
