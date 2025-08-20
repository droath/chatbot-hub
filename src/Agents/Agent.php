<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Illuminate\Support\Arr;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Messages\MessageBase;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;

class Agent implements AgentInterface
{
    protected array $input = [];

    protected ?string $modal = null;

    protected array $responseFormat = [];

    protected ?AgentMemoryInterface $memory = null;

    protected ?ResourceInterface $resource = null;

    /**
     * Define the agent constructor.
     */
    protected function __construct(
        string|array $input,
        protected array $tools
    ) {
        $this->input = ! is_array($input)
            ? [UserMessage::make($input)]
            : $input;
    }

    /**
     * {@inheritDoc}
     */
    public static function make(
        string|array $input = [],
        array $tools = []
    ): self {
        return new self($input, $tools);
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(
        ChatbotHubResponseMessage $response,
        \Closure $next
    ): ?ChatbotHubResponseMessage {
        $innerResource = $this->addInput((string) $response)->run();

        return $next($innerResource);
    }

    /**
     * {@inheritDoc}
     */
    public function addInput(string|MessageBase $input): static
    {
        $this->input[] = is_string($input)
            ? UserMessage::make($input)
            : $input;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addInputs(array $input): static
    {
        foreach ($input as $message) {
            $this->addInput($message);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getInputs(): array
    {
        return $this->input;
    }

    /**
     * {@inheritDoc}
     */
    public function addTool(Tool $tool): static
    {
        $this->tools[] = $tool;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function addTools(array $tools): static
    {
        foreach ($tools as $tool) {
            if (! $tool instanceof Tool) {
                continue;
            }
            $this->addTool($tool);
        }

        return $this;
    }

    /**
     * Set the modal to use for the agent.
     *
     * @return $this
     */
    public function setModal(?string $modal): static
    {
        $this->modal = $modal;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setSystemPrompt(string $prompt): static
    {
        $this->input = Arr::prepend(
            $this->input,
            SystemMessage::make($prompt)
        );

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setResponseFormat(array $format): static
    {
        $this->responseFormat[] = $format;

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
    public function setResource(ResourceInterface $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function asTool(ResourceInterface $resource): Tool
    {
        return Tool::make('testing')->using(function () use ($resource) {
            return $this->run($resource);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function run(
        ?ResourceInterface $resource = null
    ): ChatbotHubResponseMessage {
        $resource = $this->resource($resource);

        if (! isset($resource)) {
            throw new \RuntimeException(
                'Resource is not set.'
            );
        }

        if ($model = $this->modal) {
            $resource->withModel($model);
        }

        if ($resource instanceof HasToolsInterface) {
            $resource->withTools($this->tools);
        }

        if ($resource instanceof HasMessagesInterface) {
            $resource->withMessages($this->input);
        }

        if ($resource instanceof HasResponseFormatInterface) {
            $resource->withResponseFormat($this->responseFormat);
        }

        return $resource->__invoke();
    }

    /**
     * Resolve the resource instance.
     */
    protected function resource(?ResourceInterface $default): ?ResourceInterface
    {
        return $this->resource ?? $default;
    }
}
