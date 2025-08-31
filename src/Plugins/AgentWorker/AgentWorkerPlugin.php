<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Illuminate\Support\Str;
use Droath\ChatbotHub\Agents\Agent;
use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\PluginManager\Plugin\PluginBase;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Agents\AgentCoordinator;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Plugins\AgentToolPluginManager;
use Droath\ChatbotHub\Agents\Contracts\AgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\PluginManager\Exceptions\PluginNotFoundException;
use Droath\ChatbotHub\Agents\ValueObject\AgentCoordinatorResponse;
use Droath\ChatbotHub\Plugins\Contracts\AgentWorkerPluginInterface;

/**
 * Define the agent worker plugin base.
 */
abstract class AgentWorkerPlugin extends PluginBase implements AgentWorkerPluginInterface
{
    protected ChatbotProvider $defaultProvider = ChatbotProvider::OPENAI;

    /**
     * {@inheritDoc}
     */
    public function respond(
        string|array $message = [],
        array $tools = []
    ): AgentCoordinatorResponse|array {
        try {
            if ($agent = $this->createAgent()) {
                $response = AgentCoordinator::make(
                    $message,
                    [$agent],
                    AgentStrategy::PARALLEL
                )->run($this->resourceInstance());

                return [
                    $this->handleResponse($response),
                ];
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

        return [];
    }

    /**
     * Define the agent provider model.
     */
    protected function model(): ?string
    {
        return null;
    }

    /**
     * Define the agent response format.
     */
    protected function responseFormat(): array
    {
        return [];
    }

    /**
     * Define the agent worker messages.
     */
    protected function messages(): array
    {
        return array_filter([
            $this->systemInstruction(),
            $this->userInstruction(),
        ]);
    }

    /**
     * Define the agent default tools.
     */
    protected function tools(): array
    {
        $manager = app(AgentToolPluginManager::class);

        return collect($this->pluginDefinition['tools'] ?? [])
            ->map(function ($name) use ($manager) {
                if (is_string($name)) {
                    try {
                        /** @var \Droath\ChatbotHub\Plugins\Contracts\AgentToolPluginInterface $instance */
                        if ($instance = $manager->createInstance($name)) {
                            return $instance->definition();
                        }
                    } catch (PluginNotFoundException) {
                        return $name;
                    }
                }

                return $name;
            })
            ->filter()
            ->all();
    }

    /**
     * Create the chat agent instance.
     */
    protected function createAgent(): AgentInterface
    {
        return Agent::make()
            ->setModal($this->model())
            ->addTools($this->tools())
            ->addInputs($this->messages())
            ->setResponseFormat($this->responseFormat());
    }

    /**
     * Retrieve the resource to use for the agent.
     */
    protected function resourceInstance(): ResourceInterface
    {
        return ChatbotHub::chat($this->provider());
    }

    /**
     * Resolve the agent plugin chatbot provider.
     */
    protected function provider(): ?ChatbotProvider
    {
        $provider = $this->pluginDefinition['provider'] ?? null;

        return $provider instanceof ChatbotProvider
            ? $provider
            : $this->defaultProvider;
    }

    /**
     * Handler for the chat agent response.
     *
     * @param \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage $response
     *   The chat agent response message object.
     *
     * @throws \JsonException
     */
    protected function handleResponse(
        ChatbotHubResponseMessage $response
    ): mixed {
        return $this->handleResponseMessage(
            $this->transformResponseMessage($response)
        );
    }

    /**
     * Transform the response message returned from the chat agent.
     *
     * @param \Droath\ChatbotHub\Responses\ChatbotHubResponseMessage $response
     *   The chatbot response message object.
     *
     * @return string|array
     *   The transformed response message.
     *
     * @throws \JsonException
     */
    protected function transformResponseMessage(
        ChatbotHubResponseMessage $response
    ): string|array {
        $message = $this->normalizeResponseMessage(
            $response->message
        );

        if (Str::isJson($message)) {
            return json_decode(
                $message,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        return $message;
    }

    /**
     * Normalize the response message.
     */
    protected function normalizeResponseMessage(
        array|string $message
    ): array|string {
        return $message;
    }

    /**
     * Define the agent user message
     */
    protected function userInstruction(): ?UserMessage
    {
        return null;
    }

    /**
     * Define the agent system message.
     */
    abstract protected function systemInstruction(): ?SystemMessage;

    /**
     * Handler for the chat agent response message.
     */
    abstract protected function handleResponseMessage(string|array $message): mixed;
}
