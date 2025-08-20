<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

use Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface;

interface HasResponsesInterface
{
    /**
     * Define the LLM responses resource.
     */
    public function responses(): ResponsesResourceInterface;
}
