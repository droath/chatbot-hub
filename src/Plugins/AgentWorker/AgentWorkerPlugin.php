<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Droath\ChatbotHub\Agents\ChatAgent;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Plugins\Contracts\AgentPluginWorkerInterface;
use Droath\PluginManager\Plugin\BasePlugin;

/**
 * Define the agent worker plugin base.
 */
abstract class AgentWorkerPlugin extends BasePlugin implements AgentPluginWorkerInterface
{
    protected ChatbotProvider $defaultModel = ChatbotProvider::OPENAI;

    public function createAgent(): AgentInterface
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

    protected function messages(): array
    {
        return [
            SystemMessage::make($this->systemPrompt()),
        ];
    }

    /**
     * Get the agent worker plugin model.
     */
    protected function model(): ?ChatbotProvider
    {
        return $this->pluginDefinition['model'] ?? $this->defaultModel;
    }

    /**
     * Define the agent worker system prompt.
     */
    abstract protected function systemPrompt(): string;
}
