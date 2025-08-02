<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;

/**
 * Define the agent interface.
 */
interface AgentInterface
{
    /**
     * Run the agent implementation.
     */
    public function run(): ChatbotHubResponseMessage;

    /**
     * Set the agent resource instance.
     *
     * @param \Droath\ChatbotHub\Resources\Contracts\ResourceInterface $resource
     *
     * @return \Droath\ChatbotHub\Agents\Contracts\AgentInterface
     */
    public function setResourceInstance(ResourceInterface $resource): static;
}
