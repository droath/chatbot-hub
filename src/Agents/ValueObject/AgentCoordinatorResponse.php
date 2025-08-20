<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\ValueObject;

use Illuminate\Support\Collection;

class AgentCoordinatorResponse
{
    private function __construct(
        protected array $agents,
        protected array $responses,
    ) {}

    public static function make(array $responses, array $agents = []): self
    {
        return new self($agents, $responses);
    }

    public function getAgents(): Collection
    {
        return collect($this->agents);
    }

    public function getResponses(): Collection
    {
        return collect($this->responses);
    }
}
