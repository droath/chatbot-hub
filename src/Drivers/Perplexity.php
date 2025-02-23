<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;
use Droath\ChatbotHub\Resources\PerplexityChatResource;
use SoftCreatR\PerplexityAI\PerplexityAI;

class Perplexity extends ChatbotHubDriver implements HasChatInterface
{
    /**
     * Define the default modal to use.
     */
    public const string DEFAULT_MODEL = 'sonar';

    public function __construct(
        protected PerplexityAI $client
    ) {}

    /**
     * {@inheritDoc}
     */
    public function chat(): ChatResourceInterface
    {
        return new PerplexityChatResource($this->client, $this);
    }
}
