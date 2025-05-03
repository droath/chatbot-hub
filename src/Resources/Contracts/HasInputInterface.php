<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

interface HasInputInterface
{
    /**
     * @param string|array $input
     *
     * @return $this
     */
    public function withInput(string|array $input): static;
}
