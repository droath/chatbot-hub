<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\ChatbotHub\Agents\Agent;

/**
 * Define the agent plugin interface.
 */
interface AgentPluginInterface
{
    /**
     * Executes the agent and returns its response.
     *
     * @return mixed
     *   The agents' response.
     */
    public function run(): mixed;

    /**
     * Create an agent instance object.
     */
    public function createInstance(string|array $input = []): Agent;
}
