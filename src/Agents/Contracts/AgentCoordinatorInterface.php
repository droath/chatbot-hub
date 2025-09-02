<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse;

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
        AgentStrategy $strategy
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
     * Set the system prompt for the agent coordinator.
     *
     * @return $this
     */
    public function setSystemPrompt(string $prompt): static;

    /**
     * Set the response format for the agent coordinator.
     *
     * @return $this
     */
    public function setResponseFormat(array $format): static;

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
