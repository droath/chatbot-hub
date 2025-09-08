<?php

declare(strict_types=1);

namespace Droath\ChatbotHub\Memory\Exceptions;

/**
 * Exception thrown when memory strategy operations fail.
 * 
 * Used for strategy-specific errors like connection failures,
 * health check failures, or strategy unavailability.
 */
class MemoryStrategyException extends MemoryException
{
    /**
     * Create an exception for strategy health check failure.
     */
    public static function unhealthy(string $strategy, string $reason = '', ?\Throwable $previous = null): static
    {
        $message = "Memory strategy '{$strategy}' is unhealthy";
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        return static::forStrategy($strategy, $message, $previous);
    }

    /**
     * Create an exception for strategy connection failure.
     */
    public static function connectionFailed(string $strategy, string $details = '', ?\Throwable $previous = null): static
    {
        $message = "Failed to connect to memory strategy '{$strategy}'";
        if ($details) {
            $message .= ": {$details}";
        }
        
        return static::forStrategy($strategy, $message, $previous);
    }

    /**
     * Create an exception for strategy unavailability.
     */
    public static function unavailable(string $strategy, string $reason = '', ?\Throwable $previous = null): static
    {
        $message = "Memory strategy '{$strategy}' is not available";
        if ($reason) {
            $message .= ": {$reason}";
        }
        
        return static::forStrategy($strategy, $message, $previous);
    }

    /**
     * Create an exception for strategy creation failure.
     */
    public static function creationFailed(string $strategy, ?\Throwable $previous = null): static
    {
        return static::forStrategy(
            $strategy,
            "Failed to create memory strategy instance: {$strategy}",
            $previous
        );
    }
}