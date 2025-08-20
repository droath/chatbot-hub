<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ResponsesResourceInterface extends ResourceInterface
{
    public function __invoke(): ?ChatbotHubResponseMessage;
}
