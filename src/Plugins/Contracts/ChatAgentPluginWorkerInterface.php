<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Contracts;

use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\PluginManager\Contracts\PluginInterface;

/**
 * Define the agent plugin worker interface.
 */
interface ChatAgentPluginWorkerInterface extends PluginInterface
{
    /**
     * Invoke the chat agent response.
     *
     * @param array $messages
     *   An array of messages to send to the chat agent.
     * @param array $tools
     *   An array of tools to send to the chat agent.
     *
     * @return mixed
     */
    public function response(array $messages = [], array $tools = []): mixed;

    /**
     * Define the chat agent system message.
     */
    public function systemInstruction(): SystemMessage;
}
