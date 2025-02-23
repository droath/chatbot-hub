<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Drivers;

use SoftCreatR\PerplexityAI\PerplexityAI;
use Droath\ChatbotHub\Resources\PerplexityChatResource;
use Droath\ChatbotHub\Drivers\Contracts\HasChatInterface;
use Droath\ChatbotHub\Resources\Contracts\ChatResourceInterface;

class Perplexity extends ChatbotHubDriver implements HasChatInterface
{
    /**
     * Define the default modal to use.
     */
    public const string DEFAULT_MODEL = 'sonar';

    /**
     * @param \SoftCreatR\PerplexityAI\PerplexityAI $client
     */
    public function __construct(
        protected PerplexityAI $client
    ) {}

    /**
     * @inheritDoc
     */
    public function chat(): ChatResourceInterface
    {
        return new PerplexityChatResource($this->client, $this);
    }
}
