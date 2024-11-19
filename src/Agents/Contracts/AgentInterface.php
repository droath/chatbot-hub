<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;

/**
 * Define the agent interface.
 */
interface AgentInterface
{
    /**
     * Run the agent implementation.
     */
    public function run(): ChatbotHubResponseMessage;
}
