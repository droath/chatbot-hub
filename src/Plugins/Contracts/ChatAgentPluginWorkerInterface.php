<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\PluginManager\Contracts\PluginInterface;

/**
 * Define the agent plugin worker interface.
 */
interface ChatAgentPluginWorkerInterface extends PluginInterface
{
    /**
     * Create a chat agent instance.
     */
    public function createAgent(): ChatAgentInterface;
}
