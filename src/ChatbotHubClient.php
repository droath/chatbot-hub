<?php

declare(strict_types=1);

namespace Droath\ChatbotHub;

use Illuminate\Support\Manager;
use Droath\ChatbotHub\Drivers\Openai;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;

/**
 * Define the chatbot hub client class.
 */
class ChatbotHubClient extends Manager
{
    /**
     * @inheritDoc
     */
    public function getDefaultDriver(): string
    {
        return ChatbotProvider::OPENAI->value;
    }

    /**
     * Create teh Openai client class.
     *
     * @return \Droath\ChatbotHub\Drivers\Openai
     */
    protected function createOpenaiDriver(): Openai
    {
        $client = \OpenAI::factory()
            ->withApiKey(config('chatbot-hub.openai.api_key'))
            ->withBaseUri(config('chatbot-hub.openai.base_url'))
            ->withOrganization(config('chatbot-hub.openai.organization'))
            ->make();

        return new Openai($client);
    }
}
