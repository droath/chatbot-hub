<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Memory\Configuration;

use InvalidArgumentException;

/**
 * Memory system configuration manager.
 * 
 * Handles configuration validation and provides typed access to memory
 * system settings for different strategies.
 */
class MemoryConfiguration
{
    /**
     * Valid memory strategies.
     */
    public const VALID_STRATEGIES = ['session', 'database', 'null'];

    /**
     * Default TTL in seconds (1 hour).
     */
    public const DEFAULT_TTL = 3600;

    /**
     * Default memory strategy.
     */
    public const DEFAULT_STRATEGY = 'session';

    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $this->validateAndNormalizeConfig($config);
    }

    /**
     * Get the default memory strategy.
     */
    public function getDefaultStrategy(): string
    {
        return $this->config['default'] ?? self::DEFAULT_STRATEGY;
    }

    /**
     * Get configuration for a specific strategy.
     */
    public function getStrategyConfig(string $strategy): array
    {
        $this->validateStrategy($strategy);
        
        return $this->config['strategies'][$strategy] ?? [];
    }

    /**
     * Get the default TTL for memory entries.
     */
    public function getDefaultTtl(): int
    {
        return $this->config['default_ttl'] ?? self::DEFAULT_TTL;
    }

    /**
     * Check if a strategy is enabled.
     */
    public function isStrategyEnabled(string $strategy): bool
    {
        $this->validateStrategy($strategy);
        
        $strategyConfig = $this->getStrategyConfig($strategy);
        
        return $strategyConfig['enabled'] ?? true;
    }

    /**
     * Get all configured strategies.
     */
    public function getAvailableStrategies(): array
    {
        return array_keys($this->config['strategies'] ?? []);
    }

    /**
     * Get the full configuration array.
     */
    public function toArray(): array
    {
        return $this->config;
    }

    /**
     * Validate that a strategy is supported.
     */
    protected function validateStrategy(string $strategy): void
    {
        if (!in_array($strategy, self::VALID_STRATEGIES, true)) {
            throw new InvalidArgumentException(
                "Invalid memory strategy '{$strategy}'. Valid strategies: " . 
                implode(', ', self::VALID_STRATEGIES)
            );
        }
    }

    /**
     * Validate and normalize the configuration array.
     */
    protected function validateAndNormalizeConfig(array $config): array
    {
        // Ensure we have a strategies array
        if (!isset($config['strategies'])) {
            $config['strategies'] = [];
        }

        // Validate default strategy if set
        if (isset($config['default'])) {
            $this->validateStrategy($config['default']);
        }

        // Validate default TTL if set
        if (isset($config['default_ttl']) && (!is_int($config['default_ttl']) || $config['default_ttl'] < 0)) {
            throw new InvalidArgumentException('default_ttl must be a non-negative integer');
        }

        // Validate each strategy configuration
        foreach ($config['strategies'] as $strategy => $strategyConfig) {
            $this->validateStrategy($strategy);
            $config['strategies'][$strategy] = $this->validateStrategyConfig($strategy, $strategyConfig);
        }

        return $config;
    }

    /**
     * Validate configuration for a specific strategy.
     */
    protected function validateStrategyConfig(string $strategy, array $config): array
    {
        return match ($strategy) {
            'session' => $this->validateSessionConfig($config),
            'database' => $this->validateDatabaseConfig($config),
            'null' => $this->validateNullConfig($config),
            default => $config, // Unknown strategies pass through for extensibility
        };
    }

    /**
     * Validate session strategy configuration.
     */
    protected function validateSessionConfig(array $config): array
    {
        $defaults = [
            'enabled' => true,
            'prefix' => 'agent_memory',
        ];

        $config = array_merge($defaults, $config);

        if (!is_string($config['prefix'])) {
            throw new InvalidArgumentException('Session strategy prefix must be a string');
        }

        if (!is_bool($config['enabled'])) {
            throw new InvalidArgumentException('Session strategy enabled must be a boolean');
        }

        return $config;
    }

    /**
     * Validate database strategy configuration.
     */
    protected function validateDatabaseConfig(array $config): array
    {
        $defaults = [
            'enabled' => true,
            'connection' => null, // Use default Laravel connection
            'table' => 'agent_memory',
            'cleanup_probability' => 100, // Always cleanup expired entries
        ];

        $config = array_merge($defaults, $config);

        if ($config['connection'] !== null && !is_string($config['connection'])) {
            throw new InvalidArgumentException('Database strategy connection must be a string or null');
        }

        if (!is_string($config['table'])) {
            throw new InvalidArgumentException('Database strategy table must be a string');
        }

        if (!is_int($config['cleanup_probability']) || $config['cleanup_probability'] < 0 || $config['cleanup_probability'] > 100) {
            throw new InvalidArgumentException('Database strategy cleanup_probability must be an integer between 0 and 100');
        }

        if (!is_bool($config['enabled'])) {
            throw new InvalidArgumentException('Database strategy enabled must be a boolean');
        }

        return $config;
    }

    /**
     * Validate null strategy configuration.
     */
    protected function validateNullConfig(array $config): array
    {
        $defaults = [
            'enabled' => true,
        ];

        $config = array_merge($defaults, $config);

        if (!is_bool($config['enabled'])) {
            throw new InvalidArgumentException('Null strategy enabled must be a boolean');
        }

        return $config;
    }
}