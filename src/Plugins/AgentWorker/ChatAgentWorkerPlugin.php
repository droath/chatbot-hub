<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Droath\ChatbotHub\Agents\ChatAgent;
use Droath\PluginManager\Plugin\BasePlugin;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Plugins\Contracts\ChatAgentPluginWorkerInterface;

/**
 * Define the agent worker plugin base.
 */
abstract class ChatAgentWorkerPlugin extends BasePlugin implements ChatAgentPluginWorkerInterface
{
    /**
     * @var \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider
     */
    protected ChatbotProvider $defaultProvider = ChatbotProvider::OPENAI;

    /**
     * {@inheritDoc}
     */
    public function createAgent(): ChatAgentInterface
    {
        return ChatAgent::make(
            $this->provider(),
            $this->messages(),
            $this->tools(),
            $this->responseFormat()
        );
    }

    /**
     * Define the agent default tools.
     */
    protected function tools(): array
    {
        return [];
    }

    /**
     * Define the agent worker messages.
     */
    protected function messages(): array
    {
        return [
            $this->systemInstruction(),
        ];
    }

    /**
     * Define the agent worker response format.
     *
     * @return array
     */
    protected function responseFormat(): array
    {
        return [];
    }

    /**
     * Get the agent worker plugin provider.
     */
    protected function provider(): ?ChatbotProvider
    {
        $provider = $this->pluginDefinition['provider'] ?? null;

        if ($provider instanceof ChatbotProvider) {
            return $provider;
        }

        return $this->defaultProvider;
    }

    /**
     * Define the agent worker system instruction message.
     */
    abstract protected function systemInstruction(): SystemMessage;
}
