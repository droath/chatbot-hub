<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

interface ResourceInterface extends HasModelInterface
{
    /**
     * Invoke the resource response.
     */
    public function __invoke(): mixed;
}
