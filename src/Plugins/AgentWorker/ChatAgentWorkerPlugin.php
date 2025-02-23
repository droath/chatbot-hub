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
    protected ChatbotProvider $defaultModel = ChatbotProvider::OPENAI;

    /**
     * @inheritDoc
     */
    public function createAgent(): ChatAgentInterface
    {
        return ChatAgent::make(
            $this->model(),
            $this->messages(),
            $this->tools(),
        );
    }

    /**
     * Get the agent worker plugin tools.
     */
    protected function tools(): array
    {
        return [];
    }

    /**
     * Define the agent worker messages.
     *
     * @return array
     */
    protected function messages(): array
    {
        return [
            $this->systemInstruction()
        ];
    }

    /**
     * Get the agent worker plugin model.
     */
    protected function model(): ?ChatbotProvider
    {
        return ChatbotProvider::tryFrom($this->pluginDefinition['model'])
            ?? $this->defaultModel;
    }

    /**
     * Define the agent worker system instruction message.
     */
    abstract protected function systemInstruction(): SystemMessage;
}
