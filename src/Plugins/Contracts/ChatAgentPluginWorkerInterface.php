<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\PluginManager\Contracts\PluginInterface;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;

/**
 * Define the agent plugin worker interface.
 */
interface ChatAgentPluginWorkerInterface extends PluginInterface
{
    /**
     * Get chat agent response.
     *
     * @param array $messages
     * @param array $tools
     *
     * @return mixed
     */
    public function response(
        array $messages = [],
        array $tools = []
    ): mixed;

    /**
     * Create a chat agent instance.
     */
    public function createAgent(): ChatAgentInterface;
}
