<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Illuminate\Support\Facades\Pipeline;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse;

/**
 * Define the agent strategy executor.
 */
final class AgentCoordinatorStrategyExecutor
{
    /**
     * @param \Droath\ChatbotHub\Agents\Agent[] $agents
     *   The agents to execute.
     * @param \Droath\ChatbotHub\Agents\Enums\AgentStrategy $strategy
     *   The agent executing strategy.
     * @param \Droath\ChatbotHub\Resources\Contracts\ResourceInterface $resource
     *   The agent LLM resource instance.
     */
    public function __construct(
        protected array $agents,
        protected AgentStrategy $strategy,
        protected ResourceInterface $resource,
    ) {}

    public function handle(): AgentCoordinatorResponse
    {
        return match ($this->strategy) {
            AgentStrategy::ROUTER => $this->handleRouter(),
            AgentStrategy::PARALLEL => $this->handleParallel(),
            AgentStrategy::SEQUENTIAL => $this->handleSequential(),
        };
    }

    protected function handleRouter(): AgentCoordinatorResponse
    {
        $resource = $this->resource;

        if ($resource instanceof HasToolsInterface) {
            $resource->withTools(
                $this->agentsAsTools()
            );
        }
        $coordinatorResponse = $resource();

        return AgentCoordinatorResponse::make(
            agents: $this->agents,
            strategy: $this->strategy,
            resource: $this->resource,
            coordinatorResponse: $coordinatorResponse,
        );
    }

    protected function handleParallel(): AgentCoordinatorResponse
    {
        $agentResponses = [];

        foreach ($this->agents as $index => $agent) {
            $agentResponses[$agent->name ?? $index] = $agent->run(
                clone $this->resource
            );
        }
        $messages = $this->toUserMessages($agentResponses);

        $coordinatorResponse = $this->invokeCoordinatorResource(
            $messages
        );

        return AgentCoordinatorResponse::make(
            agents: $this->agents,
            strategy: $this->strategy,
            resource: $this->resource,
            coordinatorResponse: $coordinatorResponse,
            agentResponses: $agentResponses
        );
    }

    protected function handleSequential(): AgentCoordinatorResponse
    {
        $agents = $this->agents;
        $agent = array_shift($this->agents);

        if ($agent instanceof AgentInterface) {
            $response = $agent->run(clone $this->resource);

            $message = Pipeline::send($response)
                ->through($this->agents)
                ->thenReturn();

            $coordinatorResponse = $this->invokeCoordinatorResource(
                [$message]
            );

            return AgentCoordinatorResponse::make(
                agents: $agents,
                strategy: $this->strategy,
                resource: $this->resource,
                coordinatorResponse: $coordinatorResponse,
                agentResponses: [$message]
            );
        }

        return AgentCoordinatorResponse::make(
            agents: $this->agents,
            strategy: $this->strategy,
            resource: $this->resource,
            coordinatorResponse: null
        );
    }

    /**
     * Invoke the coordinator resource to get the response.
     */
    protected function invokeCoordinatorResource(
        array $messages
    ): ?ChatbotHubResponseMessage {
        if ($this->resource instanceof HasMessagesInterface) {
            return $this->resource->withMessages($messages)->__invoke();
        }

        return null;
    }

    /**
     * @return \Droath\ChatbotHub\Tools\Tool[]
     */
    protected function agentsAsTools(): array
    {
        $tools = [];

        foreach ($this->agents as $agent) {
            $tools[] = $agent->asTool();
        }

        return array_filter($tools);
    }

    protected function toUserMessages(array $responses): array
    {
        return collect($responses)
            ->transform(function ($response) {
                $response = is_array($response)
                    ? json_encode($response, JSON_THROW_ON_ERROR)
                    : $response->__toString();

                return UserMessage::make($response);
            })->toArray();
    }
}
