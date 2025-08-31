<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Agents\ValueObject;

use Illuminate\Support\Collection;
use Droath\ChatbotHub\Agents\Enums\AgentStrategy;
use Droath\ChatbotHub\Responses\ChatbotHubResponseMessage;
use Droath\ChatbotHub\Resources\Contracts\ResourceInterface;

readonly class AgentCoordinatorResponse
{
    private function __construct(
        protected array $agents,
        protected AgentStrategy $strategy,
        protected ResourceInterface $resource,
        protected ?ChatbotHubResponseMessage $coordinatorResponse,
        protected array $agentResponses,
    ) {}

    public static function make(
        array $agents,
        AgentStrategy $strategy,
        ResourceInterface $resource,
        ?ChatbotHubResponseMessage $coordinatorResponse,
        array $agentResponses = []
    ): self {
        return new self(
            $agents,
            $strategy,
            $resource,
            $coordinatorResponse,
            $agentResponses,
        );
    }

    public function assertStrategyEqual(
        AgentStrategy $expectedStrategy
    ): self {
        expect($this->strategy)->toEqual($expectedStrategy);

        return $this;
    }

    public function assertAgentsUsing(\Closure $handler): self
    {
        $response = $handler(
            collect($this->agents)
        );

        expect($response)->toBeTrue();

        return $this;
    }

    public function assertResourceUsing(\Closure $handler): self
    {
        $response = $handler(
            $this->resource
        );

        expect($response)->toBeTrue();

        return $this;
    }

    public function agentResponses(): Collection
    {
        return collect($this->agentResponses);
    }

    public function coordinatorResponse(): ChatbotHubResponseMessage
    {
        return $this->coordinatorResponse;
    }
}
