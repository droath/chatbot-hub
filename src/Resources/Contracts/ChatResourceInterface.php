<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ChatResourceInterface extends ResourceInterface
{
    /**
     * @return \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage|null
     */
    public function __invoke(): ?ChatbotHubResponseMessage;
}
