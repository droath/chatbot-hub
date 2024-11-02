<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;

interface ChatResourceInterface extends ResourceInterface
{
    /**
     * @return \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage|null
     */
    public function __invoke(): ?ChatbotHubResponseMessage;

    /**
     * @return $this
     */
    public function withTools(array $tools): static;

    /**
     * @return $this
     */
    public function withMessages(array|MessageStorageInterface $messages): static;
}
