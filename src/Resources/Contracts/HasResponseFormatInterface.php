<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

interface HasResponseFormatInterface
{
    /***
     * @return $this
     */
    public function withResponseFormat(array $responseFormat): static;
}
