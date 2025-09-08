<?php

declare(strict_types=1);

use Droath\ChatbotHub\Memory\Exceptions\MemoryException;
use Droath\ChatbotHub\Memory\Exceptions\MemoryStorageException;
use Droath\ChatbotHub\Memory\Exceptions\MemoryConfigurationException;
use Droath\ChatbotHub\Memory\Exceptions\MemoryStrategyException;

describe('MemoryException', function () {
    test('extends Exception', function () {
        $exception = new MemoryException();
        expect($exception)->toBeInstanceOf(\Exception::class);
    });

    test('can set and get memory key', function () {
        $exception = new MemoryException();
        $result = $exception->setMemoryKey('test_key');
        
        expect($result)->toBe($exception) // Fluent interface
            ->and($exception->getMemoryKey())->toBe('test_key');
    });

    test('can set and get strategy', function () {
        $exception = new MemoryException();
        $result = $exception->setStrategy('test_strategy');
        
        expect($result)->toBe($exception) // Fluent interface
            ->and($exception->getStrategy())->toBe('test_strategy');
    });

    test('forKey creates contextualized exception', function () {
        $exception = MemoryException::forKey('test_key', 'Custom message');
        
        expect($exception->getMemoryKey())->toBe('test_key')
            ->and($exception->getMessage())->toBe('Custom message');
    });

    test('forKey uses default message when empty', function () {
        $exception = MemoryException::forKey('test_key');
        
        expect($exception->getMessage())->toBe('Memory error for key: test_key');
    });

    test('forStrategy creates strategy-specific exception', function () {
        $exception = MemoryException::forStrategy('test_strategy', 'Custom message');
        
        expect($exception->getStrategy())->toBe('test_strategy')
            ->and($exception->getMessage())->toBe('Custom message');
    });

    test('forStrategy uses default message when empty', function () {
        $exception = MemoryException::forStrategy('test_strategy');
        
        expect($exception->getMessage())->toBe('Memory error in strategy: test_strategy');
    });

    test('preserves previous exception', function () {
        $previous = new \Exception('Previous error');
        $exception = MemoryException::forKey('key', 'New error', $previous);
        
        expect($exception->getPrevious())->toBe($previous);
    });
});

describe('MemoryStorageException', function () {
    test('extends MemoryException', function () {
        $exception = new MemoryStorageException();
        expect($exception)->toBeInstanceOf(MemoryException::class);
    });

    test('setFailed creates appropriate exception', function () {
        $exception = MemoryStorageException::setFailed('test_key', 'session');
        
        expect($exception->getMemoryKey())->toBe('test_key')
            ->and($exception->getStrategy())->toBe('session')
            ->and($exception->getMessage())->toBe('Failed to set memory key: test_key using session strategy');
    });

    test('getFailed creates appropriate exception', function () {
        $exception = MemoryStorageException::getFailed('test_key', 'database');
        
        expect($exception->getMemoryKey())->toBe('test_key')
            ->and($exception->getStrategy())->toBe('database')
            ->and($exception->getMessage())->toBe('Failed to get memory key: test_key using database strategy');
    });

    test('forgetFailed creates appropriate exception', function () {
        $exception = MemoryStorageException::forgetFailed('test_key');
        
        expect($exception->getMemoryKey())->toBe('test_key')
            ->and($exception->getMessage())->toBe('Failed to forget memory key: test_key');
    });

    test('flushFailed creates appropriate exception', function () {
        $exception = MemoryStorageException::flushFailed('session');
        
        expect($exception->getStrategy())->toBe('session')
            ->and($exception->getMessage())->toBe('Failed to flush memory using session strategy');
    });

    test('preserves previous exceptions', function () {
        $previous = new \Exception('DB connection lost');
        $exception = MemoryStorageException::setFailed('key', 'database', $previous);
        
        expect($exception->getPrevious())->toBe($previous);
    });
});

describe('MemoryConfigurationException', function () {
    test('extends MemoryException', function () {
        $exception = new MemoryConfigurationException();
        expect($exception)->toBeInstanceOf(MemoryException::class);
    });

    test('invalidStrategy creates appropriate exception', function () {
        $exception = MemoryConfigurationException::invalidStrategy('unknown');
        
        expect($exception->getStrategy())->toBe('unknown')
            ->and($exception->getMessage())->toBe('Invalid or unsupported memory strategy: unknown');
    });

    test('strategyDisabled creates appropriate exception', function () {
        $exception = MemoryConfigurationException::strategyDisabled('redis');
        
        expect($exception->getStrategy())->toBe('redis')
            ->and($exception->getMessage())->toBe('Memory strategy is disabled: redis');
    });

    test('missingConfiguration creates appropriate exception', function () {
        $exception = MemoryConfigurationException::missingConfiguration('api_key');
        
        expect($exception->getMemoryKey())->toBe('api_key')
            ->and($exception->getMessage())->toBe('Missing required memory configuration: api_key');
    });

    test('invalidConfiguration creates appropriate exception', function () {
        $exception = MemoryConfigurationException::invalidConfiguration('timeout', 'invalid', 'integer');
        
        expect($exception->getMemoryKey())->toBe('timeout')
            ->and($exception->getMessage())->toBe('Invalid memory configuration value for \'timeout\': string, expected: integer');
    });
});

describe('MemoryStrategyException', function () {
    test('extends MemoryException', function () {
        $exception = new MemoryStrategyException();
        expect($exception)->toBeInstanceOf(MemoryException::class);
    });

    test('unhealthy creates appropriate exception', function () {
        $exception = MemoryStrategyException::unhealthy('database', 'Connection timeout');
        
        expect($exception->getStrategy())->toBe('database')
            ->and($exception->getMessage())->toBe('Memory strategy \'database\' is unhealthy: Connection timeout');
    });

    test('connectionFailed creates appropriate exception', function () {
        $exception = MemoryStrategyException::connectionFailed('redis', 'Host unreachable');
        
        expect($exception->getStrategy())->toBe('redis')
            ->and($exception->getMessage())->toBe('Failed to connect to memory strategy \'redis\': Host unreachable');
    });

    test('unavailable creates appropriate exception', function () {
        $exception = MemoryStrategyException::unavailable('session', 'Session store not configured');
        
        expect($exception->getStrategy())->toBe('session')
            ->and($exception->getMessage())->toBe('Memory strategy \'session\' is not available: Session store not configured');
    });

    test('creationFailed creates appropriate exception', function () {
        $exception = MemoryStrategyException::creationFailed('database');
        
        expect($exception->getStrategy())->toBe('database')
            ->and($exception->getMessage())->toBe('Failed to create memory strategy instance: database');
    });

    test('handles empty reason gracefully', function () {
        $exception = MemoryStrategyException::unhealthy('database');
        
        expect($exception->getMessage())->toBe('Memory strategy \'database\' is unhealthy');
    });
});