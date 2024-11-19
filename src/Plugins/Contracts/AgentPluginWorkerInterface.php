<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\PluginManager\Contracts\PluginInterface;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;

/**
 * Define the agent plugin worker interface.
 */
interface AgentPluginWorkerInterface extends PluginInterface
{
    /**
     * Create an agent instance.
     *
     * @return \Droath\ChatbotHub\Agents\Contracts\AgentInterface
     */
    public function createAgent(): AgentInterface;
}
