<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\Agents;

use Illuminate\Support\Str;
use Droath\ChatbotHub\Agents\Agent;
use Droath\PluginManager\Plugin\PluginBase;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Plugins\Concerns\HasChatbotResource;
use Droath\ChatbotHub\Plugins\Contracts\AgentPluginInterface;

abstract class AgentPlugin extends PluginBase implements AgentPluginInterface
{
    use HasChatbotResource;

    /**
     * {@inheritDoc}
     */
    public function run(): mixed
    {
        return $this->createInstance()->run($this->resource());
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance(string|array $input = []): Agent
    {
        $agent = Agent::make(
            input: $input,
            tools: $this->tools(),
            name: Str::snake($this->getPluginId()),
            description: $this->pluginDefinition['description'] ?? null,
        );

        if ($instruction = $this->instruction()) {
            $agent->addInput($instruction);
        }

        if ($formatResponse = $this->formatResponse()) {
            $agent->setResponseFormat($formatResponse);
        }

        if ($systemInstruction = $this->systemInstruction()) {
            $agent->setSystemPrompt($systemInstruction);
        }

        $agent->transformResponseUsing(function ($response) {
            return $this->transformResponse($response);
        });

        return $agent;
    }

    /**
     * Define the agent system instruction.
     */
    abstract protected function systemInstruction(): SystemMessage|string|null;

    /**
     * Define the agent-specific instruction.
     */
    protected function instruction(): ?string
    {
        return null;
    }

    /**
     * Define the agent tools.
     */
    protected function agentTools(): array
    {
        return [];
    }

    /**
     * Define the agent format response definition.
     */
    protected function formatResponse(): array
    {
        return [];
    }

    /**
     * Define agent transform response.
     */
    protected function transformResponse(
        ChatbotHubResponseMessage|array $response
    ): mixed {
        return $response;
    }

    private function tools(): array
    {
        return [
            ...$this->agentTools(),
            ...$this->pluginDefinition['tools'] ?? [],
        ];
    }
}
