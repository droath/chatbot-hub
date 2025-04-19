<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Plugins\AgentWorker;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Droath\ChatbotHub\Agents\ChatAgent;
use Droath\PluginManager\Plugin\PluginBase;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Agents\Contracts\ChatAgentInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Plugins\Contracts\ChatAgentPluginWorkerInterface;

/**
 * Define the agent worker plugin base.
 */
abstract class ChatAgentWorkerPlugin extends PluginBase implements ChatAgentPluginWorkerInterface
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
            $this->responseFormat()
        );
    }

    /**
     * Define the agent default tools.
     */
    protected function tools(): array
    {
        return [];
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
        if (Str::isJson($response->message)) {
            return json_decode(
                $response->message,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

        return $response->message;
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
