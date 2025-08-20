<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Droath\ChatbotHub\Agents\Contracts\AgentCoordinatorInterface;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;

class AgentCoordinator implements AgentCoordinatorInterface
{
    protected array $input;

    protected ?AgentMemoryInterface $memory = null;

    /**
     * Define the agent coordinator constructor.
     *
     * @param string|array $input
     *   The input user messages.
     * @param array $agents
     *   The agents on which to coordinate.
     * @param \Droath\ChatbotHub\Agents\Enums\AgentStrategy $strategy
     *   The agent coordinator strategy.
     */
    protected function __construct(
        string|array $input,
        protected array $agents,
        protected AgentStrategy $strategy,
    ) {
        $this->input = ! is_array($input)
            ? [UserMessage::make($input)]
            : $input;
    }

    /**
     * {@inheritDoc}
     */
    public static function make(
        string|array $input,
        array $agents,
        AgentStrategy $strategy = AgentStrategy::SEQUENTIAL
    ): self {
        return new self($input, $agents, $strategy);
    }

    /**
     * {@inheritDoc}
     */
    public function addAgent(AgentInterface $agent): static
    {
        $this->agents[] = $agent;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addAgents(array $agents): static
    {
        foreach ($agents as $agent) {
            $this->addAgent($agent);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setMemory(AgentMemoryInterface $memory): static
    {
        $this->memory = $memory;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function run(ResourceInterface $resource): AgentCoordinatorResponse
    {
        $agents = $this->prepareAgents($resource);
        $responses = (new AgentStrategyExecutor(
            $agents,
            $this->strategy,
            $resource
        ))->handle();

        return AgentCoordinatorResponse::make(
            $responses,
            $agents,
        );
    }

    /**
     * Prepare the agents based on strategy.
     *
     * @return \Droath\ChatbotHub\Agents\Contracts\AgentInterface[]
     */
    protected function prepareAgents(ResourceInterface $resource): array
    {
        $agents = $this->agents;

        if ($this->strategy === AgentStrategy::HANDOFF) {
            return [];
        }

        foreach ($agents as $index => $agent) {
            if (! $agent instanceof AgentInterface) {
                continue;
            }
            $agent->setResource(clone $resource);

            if ($memory = $this->memory) {
                $agent->setMemory($memory);
            }

            if (
                $this->strategy === AgentStrategy::PARALLEL
                /** @phpstan-ignore-next-line */
                || ($this->strategy === AgentStrategy::SEQUENTIAL && $index === 0)
            ) {
                $agent->addInputs($this->input);
            }
        }

        return $agents;
    }
}
