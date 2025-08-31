<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\PluginManager\Contracts\PluginInterface;
use Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse;

/**
 * Define the agent coordinator plugin interface.
 */
interface AgentCoordinatorPluginInterface extends PluginInterface
{
    /**
     * The responses from the agent coordinator.
     *
     * @return \Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse
     *    An array of the agent responses.
     */
    public function respond(
        string|array $input = []
    ): AgentCoordinatorResponse;
}
