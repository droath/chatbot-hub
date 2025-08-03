<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Agents\ChatAgent;
use Droath\ChatbotHub\Facades\ChatbotHub;
use Droath\PluginManager\Plugin\PluginBase;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Plugins\AgentToolPluginManager;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;
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
    public function response(
        array $userMessages = [],
        array $tools = []
    ): mixed
    {
        try {
            if ($agent = $this->createAgent()) {
                $response = $agent
                    ->addTools($tools)
                    ->addMessages($userMessages)
                    ->run();

                return $this->handleResponse($response);
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }

        return null;
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
            $this->agentResourceInstance()
        );
    }

    /**
     * @return \Droath\ChatbotHub\Resources\Contracts\ResourceInterface
     */
    protected function agentResourceInstance(): ResourceInterface
    {
        return ChatbotHub::chat($this->provider());
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
     * Define the agent default tools.
     */
    protected function tools(): array
    {
        $manager = app(AgentToolPluginManager::class);

        return collect($this->pluginDefinition['tools'])
            ->map(function ($name) use ($manager) {
                /** @var \Droath\ChatbotHub\Plugins\Contracts\AgentToolPluginInterface $instance */
                if ($instance = $manager->createInstance($name)) {
                    return $instance->definition();
                }
                return null;
            })
            ->filter()
            ->all();
    }

    /**
     * Define the agent worker messages.
     */
    protected function messages(): array
    {
        return [
            $this->systemInstruction(),
        ];
    }

    /**
     * Define the agent worker response format.
     *
     * @return array
     */
    protected function responseFormat(): array
    {
        return [];
    }

    /**
     * Resolve the chat agent chatbot provider.
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
     * Handler for the chat agent response message.
     *
     * @param string|array $message
     *
     * @return mixed
     */
    abstract protected function handleResponseMessage(string|array $message): mixed;
}
