<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ChatResourceInterface extends ResourceInterface
{
    /**
     * Invoke the chat resource response.
     *
     * @return \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage|null
     */
    public function __invoke(): ?ChatbotHubResponseMessage;
}
