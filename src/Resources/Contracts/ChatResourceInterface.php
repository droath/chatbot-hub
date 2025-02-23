<?php

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Messages\Contracts\MessageStorageInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

interface ChatResourceInterface extends ResourceInterface
{
    public function __invoke(): ?ChatbotHubResponseMessage;

    /**
     * @return $this
     */
    public function withTools(array $tools): static;

    /***
     * @return $this
     */
    public function withResponseFormat(array $responseFormat): static;

    /**
     * @return $this
     */
    public function withMessages(array|MessageStorageInterface $messages): static;
}
