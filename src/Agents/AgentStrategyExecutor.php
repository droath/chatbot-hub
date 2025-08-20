<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Illuminate\Support\Facades\Pipeline;

/**
 * Define the agent strategy executor.
 */
final class AgentStrategyExecutor
{
    /**
     * @param array $agents
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

    public function handle(): array
    {
        return match ($this->strategy) {
            AgentStrategy::HANDOFF => $this->handleHandoff(),
            AgentStrategy::PARALLEL => $this->handleParallel(),
            AgentStrategy::SEQUENTIAL => $this->handleSequential(),
        };
    }

    protected function handleHandoff(): array
    {
        return [];
    }

    protected function handleParallel(): array
    {
        $responses = [];

        foreach ($this->agents as $agent) {
            if (! $agent instanceof AgentInterface) {
                continue;
            }
            $responses[] = $agent->run(clone $this->resource);
        }

        return $responses;
    }

    protected function handleSequential(): array
    {
        $agent = array_shift($this->agents);

        if ($agent instanceof AgentInterface) {
            $response = $agent->run(clone $this->resource);

            $message = Pipeline::send($response)
                ->through($this->agents)
                ->thenReturn();

            return [$message];
        }

        return [];
    }
}
