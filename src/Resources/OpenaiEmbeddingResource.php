<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources;

use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Drivers\Openai;
use Droath\ChatbotHub\Resources\Concerns\WithInput;
use Droath\ChatbotHub\Resources\Concerns\WithModel;
use Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface;
use Droath\ChatbotHub\Resources\Contracts\HasDriverInterface;
use Droath\ChatbotHub\Resources\Contracts\HasInputInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseEmbeddings;
use OpenAI\Resources\Embeddings;
use OpenAI\Responses\Embeddings\CreateResponse;

class OpenaiEmbeddingResource implements EmbeddingsResourceInterface, HasDriverInterface, HasInputInterface
{
    protected string $model = Openai::DEFAULT_EMBEDDING_MODEL;

    use WithInput;
    use WithModel;

    public function __construct(
        protected Embeddings $resource,
        protected DriverInterface $driver
    ) {}

    /**
     * {@inheritDoc}
     */
    public function driver(): DriverInterface
    {
        return $this->driver;
    }

    public function __invoke(): ChatbotHubResponseEmbeddings
    {
        return $this->handleResponse(
            $this->resource->create($this->resourceParameters())
        );
    }

    protected function handleResponse(CreateResponse $response): ChatbotHubResponseEmbeddings
    {
        return ChatbotHubResponseEmbeddings::fromArray(
            $response->embeddings[0]->embedding
        );
    }

    protected function resourceParameters(): array
    {
        return array_filter([
            'model' => $this->model,
            'input' => $this->input,
        ]);
    }
}
