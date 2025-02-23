<?php

declare(strict_types=1);

namespace Droath\ChatbotHub;

use GuzzleHttp\Client;
use Illuminate\Support\Manager;
use GuzzleHttp\Psr7\HttpFactory;
use Droath\ChatbotHub\Drivers\Openai;
use Droath\ChatbotHub\Drivers\Perplexity;
use SoftCreatR\PerplexityAI\PerplexityAI;
use Droath\ChatbotHub\Drivers\Enums\ChatbotProvider;

/**
 * Define the chatbot hub client class.
 */
class ChatbotHubClient extends Manager
{
    /**
     * {@inheritDoc}
     */
    public function getDefaultDriver(): string
    {
        return ChatbotProvider::OPENAI->value;
    }

    /**
     * Create teh Openai client class.
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

    /**
     * Create the Perplexity client class.
     */
    protected function createPerplexityDriver(): Perplexity
    {
        $httpClient = new Client();
        $httpFactory = new HttpFactory();

        return new Perplexity(
            new PerplexityAI(
                $httpFactory,
                $httpFactory,
                $httpFactory,
                $httpClient,
                config('chatbot-hub.perplexity.api_key')
            )
        );
    }
}
