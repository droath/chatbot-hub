<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

interface HasToolsInterface
{
    /**
     * @return $this
     */
    public function withTools(array $tools): static;
}
