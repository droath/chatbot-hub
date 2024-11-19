<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\PluginManager\Contracts\PluginInterface;

/**
 * Define the agent plugin worker interface.
 */
interface AgentPluginWorkerInterface extends PluginInterface
{
    /**
     * Create an agent instance.
     */
    public function createAgent(): AgentInterface;
}
