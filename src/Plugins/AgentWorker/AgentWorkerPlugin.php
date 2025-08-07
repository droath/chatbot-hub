<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Agents\ChatAgent;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\PluginManager\Plugin\PluginBase;
use Droath\ChatbotHub\Messages\UserMessage;
use Droath\ChatbotHub\Messages\SystemMessage;
use Droath\ChatbotHub\Agents\AgentProcessHandler;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Plugins\AgentToolPluginManager;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
use Droath\PluginManager\Exceptions\PluginNotFoundException;
use Droath\ChatbotHub\Plugins\Contracts\AgentWorkerPluginInterface;

/**
 * Define the agent worker plugin base.
 */
abstract class AgentWorkerPlugin extends PluginBase implements AgentWorkerPluginInterface
{
    /**
     * @var \Droath\ChatbotHub\Drivers\Enums\ChatbotProvider
     */
    protected ChatbotProvider $defaultProvider = ChatbotProvider::OPENAI;

    /**
     * @inheritDoc
     */
    public function respond(
        array $message = [],
        array $tools = []
    ): array
    {
        try {
            if ($agent = $this->createAgent()) {
                $response = $agent
                    ->addTools($tools)
                    ->addMessages($message)
                    ->run();

                $agentResponse = $this->handleResponse($response);
                $subAgentResponses = $this->invokeSubAgentResponses($message);

                return [
                    $agentResponse,
                    ...$subAgentResponses
                ];
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

        return [];
    }

    /**
     * Define the agent provider model.
     *
     * @return string|null
     */
    protected function model(): ?string
    {
        return null;
    }

    /**
     * Define the agent response format.
     *
     * @return array
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
     * @return array
     */
    protected function registerSubAgents(): array
    {
        return [];
    }

    /**
     * Invoke the subagent responses.
     *
     * @param array|string $message
     *
     * @return array
     */
    protected function invokeSubAgentResponses(
        array|string $message
    ): array
    {
        $responses = [];

        /** @var \Droath\ChatbotHub\Agents\AgentProcessHandler $handler */
        foreach ($this->registerSubAgents() as $handler) {
            if (! $handler instanceof AgentProcessHandler) {
                continue;
            }
            $responses[] = $handler
                ->message($message)
                ->__invoke();
        }

        return $responses;
    }

    /**
     * Create the chat agent instance.
     *
     * @return \Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface
     */
    protected function createAgent(): ChatAgentInterface
    {
        return ChatAgent::make(
            $this->provider(),
            $this->messages(),
            $this->tools(),
            $this->model(),
            $this->responseFormat()
        )->setResourceInstance(
            $this->resourceInstance()
        );
    }

    /**
     * @return \Droath\ChatbotHub\Resources\Contracts\ResourceInterface
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
     * @return mixed
     * @throws \JsonException
     */
    protected function handleResponse(
        ChatbotHubResponseMessage $response
    ): mixed
    {
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
    ): string|array
    {
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
     *
     * @param array|string $message
     *
     * @return array|string
     */
    protected function normalizeResponseMessage(
        array|string $message
    ): array|string
    {
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
     *
     * @param string|array $message
     *
     * @return mixed
     */
    abstract protected function handleResponseMessage(string|array $message): mixed;
}
