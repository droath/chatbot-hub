<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\PluginManager\Contracts\PluginInterface;

/**
 * Define the agent worker plugin interface.
 */
interface AgentWorkerPluginInterface extends PluginInterface
{
    /**
     * Invoke the agent to respond.
     *
     * @param array $messages
     *   An array of messages to send to the agent.
     * @param array $tools
     *   An array of tools to send to the agent.
     *
     * @return array
     *   An array of the agent responses.
     */
    public function respond(array $messages = [], array $tools = []): array;
}
