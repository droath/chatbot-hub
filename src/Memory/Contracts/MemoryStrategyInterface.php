<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Memory\Contracts;

use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;

/**
 * Interface for memory strategy implementations.
 * 
 * Extends AgentMemoryInterface to provide strategy-specific functionality
 * for different memory storage backends (session, database, etc.).
 */
interface MemoryStrategyInterface extends AgentMemoryInterface
{
    /**
     * Get the strategy name/type.
     *
     * @return string The strategy identifier (e.g., 'session', 'database')
     */
    public function getStrategyName(): string;

    /**
     * Check if the strategy is available/healthy.
     *
     * @return bool True if the strategy can be used
     */
    public function isHealthy(): bool;

    /**
     * Get strategy-specific configuration.
     *
     * @return array Configuration array for this strategy
     */
    public function getConfiguration(): array;

    /**
     * Cleanup expired entries (strategy-specific implementation).
     *
     * @return int Number of entries cleaned up
     */
    public function cleanupExpired(): int;
}