<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Memory;

use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;
use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;
use Droath\ChatbotHub\Memory\Configuration\MemoryConfiguration;
use Droath\ChatbotHub\Memory\Strategies\SessionMemoryStrategy;
use Droath\ChatbotHub\Memory\Strategies\DatabaseMemoryStrategy;
use Droath\ChatbotHub\Memory\Strategies\NullMemoryStrategy;
use InvalidArgumentException;

/**
 * Factory for creating memory strategy instances.
 * 
 * Provides centralized creation of memory strategies with proper configuration
 * and validation.
 */
class MemoryStrategyFactory
{
    protected MemoryConfiguration $configuration;

    public function __construct(MemoryConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Create a memory strategy instance.
     */
    public function create(string $strategy): MemoryStrategyInterface
    {
        if (!$this->configuration->isStrategyEnabled($strategy)) {
            throw new InvalidArgumentException("Memory strategy '{$strategy}' is disabled");
        }

        $strategyConfig = $this->configuration->getStrategyConfig($strategy);

        return match ($strategy) {
            'session' => new SessionMemoryStrategy($strategyConfig),
            'database' => new DatabaseMemoryStrategy($strategyConfig),
            'null' => new NullMemoryStrategy($strategyConfig),
            default => throw new InvalidArgumentException("Unknown memory strategy: {$strategy}")
        };
    }

    /**
     * Create the default memory strategy.
     */
    public function createDefault(): MemoryStrategyInterface
    {
        return $this->create($this->configuration->getDefaultStrategy());
    }

    /**
     * Get available strategies.
     */
    public function getAvailableStrategies(): array
    {
        return array_filter(
            $this->configuration->getAvailableStrategies(),
            fn (string $strategy) => $this->configuration->isStrategyEnabled($strategy)
        );
    }

    /**
     * Check if a strategy is available.
     */
    public function hasStrategy(string $strategy): bool
    {
        try {
            return in_array($strategy, MemoryConfiguration::VALID_STRATEGIES, true) &&
                   $this->configuration->isStrategyEnabled($strategy);
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}