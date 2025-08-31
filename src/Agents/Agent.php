<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Droath\ChatbotHub\Tools\Tool;
use Droath\ChatbotHub\Tools\ToolProperty;
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

    protected bool $skipTransformResponse = false;

    protected ?\Closure $transformResponseHandler = null;

    /**
     * Define the agent constructor.
     */
    protected function __construct(
        string|array $input,
        protected array $tools,
        public ?string $name,
        public ?string $description,
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
        array $tools = [],
        ?string $name = null,
        ?string $description = null,
    ): self {
        return new self($input, $tools, $name, $description);
    }

    /**
     * {@inheritDoc}
     */
    public function name(): ?string
    {
        return isset($this->name)
            ? Str::snake($this->name)
            : null;
    }

    /**
     * {@inheritDoc}
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return $this
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(
        ChatbotHubResponseMessage|array $response,
        \Closure $next
    ): ChatbotHubResponseMessage|null|array {
        $response = $this->normalizeResponse($response);

        $innerResource = $this
            ->addInput($response)
            ->run();

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
    public function skipTransformResponse(): static
    {
        $this->skipTransformResponse = true;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transformResponseUsing(\Closure $handler): static
    {
        $this->transformResponseHandler = $handler;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function asTool(): ?Tool
    {
        if ($name = $this->name()) {
            $tool = Tool::make($name)
                ->using(function (array $arguments) {
                    $question = Arr::get($arguments, 'question');

                    return $this->addInput($question)->run();
                })->withProperties([
                    ToolProperty::make('question', 'string')
                        ->describe(sprintf('The question to ask the %s agent.',
                            $name
                        ))->required(),
                ]);

            if ($description = $this->description()) {
                $tool->describe($description);
            }

            return $tool;
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function run(
        ?ResourceInterface $resource = null
    ): ChatbotHubResponseMessage|array {
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

        return $this->transformResponse(
            $resource->__invoke()
        );
    }

    /**
     * Normalize the response message.
     *
     * @throws \JsonException
     */
    protected function normalizeResponse(
        ChatbotHubResponseMessage|array $response
    ): string {
        return is_array($response)
            ? (json_encode($response, JSON_THROW_ON_ERROR) ?: '')
            : $response->__toString();
    }

    /**
     * Transform the response message.
     */
    protected function transformResponse(
        ChatbotHubResponseMessage $response
    ): ChatbotHubResponseMessage|array {
        if (
            ! $this->skipTransformResponse
            && $handler = $this->transformResponseHandler
        ) {
            return $handler($response);
        }

        return $response;
    }

    /**
     * Resolve the resource instance.
     */
    protected function resource(?ResourceInterface $default): ?ResourceInterface
    {
        return $this->resource ?? $default;
    }
}
