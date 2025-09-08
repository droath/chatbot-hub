<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Memory\Strategies;

use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;

/**
 * Null memory strategy implementation.
 * 
 * No-op implementation for testing or when memory is not needed.
 * All operations succeed but no data is actually stored or retrieved.
 */
class NullMemoryStrategy implements MemoryStrategyInterface
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        // No-op implementation, always succeeds
        return true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // No-op implementation, always returns default
        return $default;
    }

    public function has(string $key): bool
    {
        // No-op implementation, nothing is ever stored
        return false;
    }

    public function forget(string $key): bool
    {
        // No-op implementation, always succeeds
        return true;
    }

    public function flush(): bool
    {
        // No-op implementation, always succeeds
        return true;
    }

    public function getStrategyName(): string
    {
        return 'null';
    }

    public function isHealthy(): bool
    {
        return true;
    }

    public function getConfiguration(): array
    {
        return $this->config;
    }

    public function cleanupExpired(): int
    {
        // No-op implementation, nothing to cleanup
        return 0;
    }
}