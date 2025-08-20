<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Testing\Resources;

use Droath\ChatbotHub\Drivers\Concerns\HasStreaming;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Drivers\Contracts\HasStreamingInterface;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;
use Droath\ChatbotHub\Drivers\Openai;
use Droath\ChatbotHub\Resources\Concerns\WithMessages;
use Droath\ChatbotHub\Resources\Concerns\WithModel;
use Droath\ChatbotHub\Resources\Concerns\WithResponseFormat;
use Droath\ChatbotHub\Resources\Concerns\WithTools;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasDriverInterface;
use Droath\ChatbotHub\Resources\Contracts\HasMessagesInterface;
use Droath\ChatbotHub\Resources\Contracts\HasResponseFormatInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolsInterface;
use Droath\ChatbotHub\Resources\Contracts\HasToolTransformerInterface;
use Droath\ChatbotHub\Resources\Contracts\ResponsesResourceInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Tools\Tool;
use OpenAI\Testing\ClientFake;

class FakeResource implements ChatResourceInterface, HasDriverInterface, HasMessagesInterface, HasResponseFormatInterface, HasStreamingInterface, HasToolsInterface, HasToolTransformerInterface, ResponsesResourceInterface
{
    use HasStreaming;
    use WithMessages;
    use WithModel;
    use WithResponseFormat;
    use WithTools;

    public function __construct(
        protected ChatbotProvider $provider,
        protected ?\Closure $responseHandler = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public static function transformTool(Tool $tool): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(): ?ChatbotHubResponseMessage
    {
        $this->invokedParameters = [
            'tools' => $this->resolveTools(),
            'messages' => $this->resolveMessages(),
            'response_format' => $this->responseFormat,
        ];

        return $this->responseHandler->__invoke();
    }

    /**
     * {@inheritDoc}
     */
    public function driver(): DriverInterface
    {
        return match ($this->provider) {
            ChatbotProvider::OPENAI => new Openai(new ClientFake([$this]))
        };
    }
}
