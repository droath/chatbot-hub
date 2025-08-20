<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\Contracts;

use Droath\ChatbotHub\Messages\MessageBase;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Tools\Tool;

/**
 * Define the agent interface.
 */
interface AgentInterface
{
    /**
     * Create the agent instance.
     */
    public static function make(array $inputs, array $tools = []): self;

    /**
     * Invoke the agent response.
     */
    public function __invoke(
        ChatbotHubResponseMessage $response,
        \Closure $next
    ): ?ChatbotHubResponseMessage;

    /**
     * Set the agent modal.
     */
    public function setModal(string $modal): static;

    /**
     * Add an input to the agent instance
     *
     * @return $this
     */
    public function addInput(string|MessageBase $input): static;

    /**
     * Add inputs to the agent instance.
     *
     * @return $this
     */
    public function addInputs(array $input): static;

    /**
     * Get the inputs for the agent instance.
     */
    public function getInputs(): array;

    /**
     * Add a tool to the agent instance.
     *
     * @return $this
     */
    public function addTool(Tool $tool): static;

    /**
     * Add tools to the agent instance.
     *
     * @return $this
     */
    public function addTools(array $tools): static;

    /**
     * Convert the agent instance to a tool.
     */
    public function asTool(ResourceInterface $resource): Tool;

    /**
     * Set the system prompt to the agent instance.
     *
     * @return $this
     */
    public function setSystemPrompt(string $prompt): static;

    /**
     * Set the response format to the agent instance.
     *
     * @return $this
     */
    public function setResponseFormat(array $format): static;

    /**
     * Set the agent memory instance.
     *
     * @return $this
     */
    public function setMemory(AgentMemoryInterface $memory): static;

    /**
     * Set the agent resource.
     *
     * @return $this
     */
    public function setResource(ResourceInterface $resource): static;

    /**
     * Run the agent implementation.
     */
    public function run(?ResourceInterface $resource = null): ?ChatbotHubResponseMessage;
}
