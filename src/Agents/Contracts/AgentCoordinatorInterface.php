<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;

/**
 * Define the agent coordinator interface.
 */
interface AgentCoordinatorInterface
{
    /**
     * Create the agent coordinator instance.
     */
    public static function make(
        string|array $input,
        array $agents,
        AgentStrategy $strategy = AgentStrategy::SEQUENTIAL
    ): self;

    /**
     * Add multiple agents to the coordinator.
     *
     * @param \Droath\ChatbotHub\Agents\Contracts\AgentInterface[] $agents
     *
     * @return $this
     */
    public function addAgents(array $agents): static;

    /**
     * Add an agent to the coordinator.
     *
     * @return $this
     */
    public function addAgent(AgentInterface $agent): static;

    /**
     * Set the agent coordinator memory.
     *
     * @return $this
     */
    public function setMemory(AgentMemoryInterface $memory): static;

    /**
     * Run the agent coordinator.
     */
    public function run(ResourceInterface $resource): AgentCoordinatorResponse;
}
