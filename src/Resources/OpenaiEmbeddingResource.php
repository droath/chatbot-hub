<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources;

use OpenAI\Resources\Embeddings;
use Droath\ChatbotHub\Drivers\Openai;
use OpenAI\Responses\Embeddings\CreateResponse;
use Droath\ChatbotHub\Resources\Concerns\WithModel;
use Droath\ChatbotHub\Resources\Concerns\WithInput;
use Droath\ChatbotHub\Drivers\Contracts\DriverInterface;
use Droath\ChatbotHub\Resources\Contracts\HasInputInterface;
use Droath\ChatbotHub\Resources\Contracts\HasDriverInterface;
use Droath\ChatbotHub\Responses\ChatbotHubResponseEmbeddings;
use Droath\ChatbotHub\Resources\Contracts\EmbeddingsResourceInterface;

class OpenaiEmbeddingResource implements EmbeddingsResourceInterface, HasDriverInterface, HasInputInterface
{
    /**
     * @var string
     */
    protected string $model = Openai::DEFAULT_EMBEDDING_MODEL;

    use WithModel;
    use WithInput;

    /**
     * @param \OpenAI\Resources\Embeddings $resource
     * @param \Droath\ChatbotHub\Drivers\Contracts\DriverInterface $driver
     */
    public function __construct(
        protected Embeddings $resource,
        protected DriverInterface $driver
    ) {}

    /**
     * @inheritDoc
     */
    public function driver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * @return \Droath\ChatbotHub\Responses\ChatbotHubResponseEmbeddings
     */
    public function __invoke(): ChatbotHubResponseEmbeddings
    {
        return $this->handleResponse(
            $this->resource->create($this->resourceParameters())
        );
    }

    /**
     * @param \OpenAI\Responses\Embeddings\CreateResponse $response
     *
     * @return \Droath\ChatbotHub\Responses\ChatbotHubResponseEmbeddings
     */
    protected function handleResponse(CreateResponse $response): ChatbotHubResponseEmbeddings
    {
        return ChatbotHubResponseEmbeddings::fromArray(
            $response->embeddings[0]->embedding
        );
    }

    /**
     * @return array
     */
    protected function resourceParameters(): array
    {
        return array_filter([
            'model' => $this->model,
            'input' => $this->input,
        ]);
    }
}
