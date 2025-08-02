<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ResponsesResourceInterface extends ResourceInterface
{
    /**
     * Invoke the responses resource response.
     *
     * @return \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage|null
     */
    public function __invoke(): ?ChatbotHubResponseMessage;
}
