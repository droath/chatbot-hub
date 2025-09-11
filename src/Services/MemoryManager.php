<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Services;

use Droath\ChatbotHub\Memory\MemoryDefinition;
use Droath\ChatbotHub\Memory\MemoryStrategyFactory;
use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;

/**
 * Manager service for creating and configuring memory strategy instances.
 *
 * Provides convenient methods for creating memory instances using the
 * configured defaults and handles common memory setup patterns.
 */
class MemoryManager
{
    /**
     * Create a memory strategy instance using the default configuration.
     */
    public function createDefault(): MemoryStrategyInterface
    {
        $strategyType = config('chatbot-hub.memory.default', 'session');

        return $this->create($strategyType);
    }

    /**
     * Create a memory strategy instance for a specific type.
     *
     * @param string $strategyType
     *   The memory strategy type (session, database, null)
     * @param array $configs
     *   Optional configuration overrides
     */
    public function create(
        string $strategyType,
        array $configs = []
    ): MemoryStrategyInterface {
        $definition = new MemoryDefinition($strategyType, $configs);
        $factory = new MemoryStrategyFactory($definition);

        return $factory->createInstance();
    }

    /**
     * Create a null memory strategy instance (for testing).
     */
    public function createNull(): MemoryStrategyInterface
    {
        return $this->create('null');
    }

    /**
     * Create a session memory strategy instance.
     */
    public function createSession(array $configs = []): MemoryStrategyInterface
    {
        return $this->create('session', $configs);
    }

    /**
     * Create a database memory strategy instance.
     */
    public function createDatabase(array $configs = []): MemoryStrategyInterface
    {
        return $this->create('database', $configs);
    }
}
