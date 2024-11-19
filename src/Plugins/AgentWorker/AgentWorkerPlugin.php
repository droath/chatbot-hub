<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Droath\ChatbotHub\Agents\ChatAgent;
use Droath\PluginManager\Plugin\BasePlugin;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Plugins\Contracts\AgentPluginWorkerInterface;

/**
 * Define the agent worker plugin base.
 */
abstract class AgentWorkerPlugin extends BasePlugin implements AgentPluginWorkerInterface
{
    /**
     * @var \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider
     */
    protected ChatbotProvider $defaultModel = ChatbotProvider::OPENAI;

    /**
     * @return \Droath\ChatbotHub\Agents\Contracts\AgentInterface
     */
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
     *
     * @return array
     */
    protected function tools(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function messages(): array
    {
        return [
            SystemMessage::make($this->systemPrompt())
        ];
    }

    /**
     * Get the agent worker plugin model.
     *
     * @return \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider|null
     */
    protected function model(): ?ChatbotProvider
    {
        return $this->pluginDefinition['model'] ?? $this->defaultModel;
    }

    /**
     * Define the agent worker system prompt.
     *
     * @return string
     */
    abstract protected function systemPrompt(): string;
}
