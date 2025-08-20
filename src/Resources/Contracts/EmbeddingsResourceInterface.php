<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Resources\Contracts;

use Droath\ChatbotHub\Responses\ChatbotHubResponseEmbeddings;

interface EmbeddingsResourceInterface extends ResourceInterface
{
    public function __invoke(): ChatbotHubResponseEmbeddings;
}
