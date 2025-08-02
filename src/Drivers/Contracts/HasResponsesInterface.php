<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers\Contracts;

use Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface;

interface HasResponsesInterface
{
    /**
     * @return \Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface
     */
    public function responses(): ResponsesResourceInterface;
}
