<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Memory\Exceptions;

/**
 * Exception thrown when memory configuration is invalid.
 * 
 * Used for errors in memory system configuration, strategy setup,
 * or invalid configuration parameters.
 */
class MemoryConfigurationException extends MemoryException
{
    /**
     * Create an exception for invalid strategy configuration.
     */
    public static function invalidStrategy(string $strategy, ?\Throwable $previous = null): static
    {
        return static::forStrategy(
            $strategy,
            "Invalid or unsupported memory strategy: {$strategy}",
            $previous
        );
    }

    /**
     * Create an exception for disabled strategy.
     */
    public static function strategyDisabled(string $strategy, ?\Throwable $previous = null): static
    {
        return static::forStrategy(
            $strategy,
            "Memory strategy is disabled: {$strategy}",
            $previous
        );
    }

    /**
     * Create an exception for missing configuration.
     */
    public static function missingConfiguration(string $key, ?\Throwable $previous = null): static
    {
        return static::forKey(
            $key,
            "Missing required memory configuration: {$key}",
            $previous
        );
    }

    /**
     * Create an exception for invalid configuration value.
     */
    public static function invalidConfiguration(string $key, mixed $value, string $expected = '', ?\Throwable $previous = null): static
    {
        $message = "Invalid memory configuration value for '{$key}': " . gettype($value);
        if ($expected) {
            $message .= ", expected: {$expected}";
        }
        
        return static::forKey($key, $message, $previous);
    }
}