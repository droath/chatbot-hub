<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ChatResourceInterface extends ResourceInterface
{
    public function __invoke(): ?ChatbotHubResponseMessage;
}
