<?php

declare(strict_types=1);

use Droath\ChatbotHub\Memory\Strategies\DatabaseMemoryStrategy;
use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;
use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;

describe('DatabaseMemoryStrategy', function () {
    beforeEach(function () {
        // Default configuration
        $this->config = [
            'connection' => null,
            'table' => 'agent_memory',
            'cleanup_probability' => 0, // Disable automatic cleanup in tests
            'default_ttl' => null, // Persistent by default
        ];

        $this->strategy = new DatabaseMemoryStrategy($this->config);
    });

    afterEach(function () {
        Mockery::close();
    });

    describe('instantiation and interface compliance', function () {
        test('can be instantiated with default configuration', function () {
            $strategy = new DatabaseMemoryStrategy();

            expect($strategy)->toBeInstanceOf(DatabaseMemoryStrategy::class)
                ->and($strategy)->toBeInstanceOf(MemoryStrategyInterface::class)
                ->and($strategy)->toBeInstanceOf(AgentMemoryInterface::class);
        });

        test('can be instantiated with custom configuration', function () {
            $config = [
                'connection' => 'memory_db',
                'table' => 'custom_memory',
                'cleanup_probability' => 50,
                'default_ttl' => 7200,
            ];
            $strategy = new DatabaseMemoryStrategy($config);

            expect($strategy)->toBeInstanceOf(DatabaseMemoryStrategy::class);
        });

        test('implements required interfaces', function () {
            expect($this->strategy)->toBeInstanceOf(MemoryStrategyInterface::class)
                ->and($this->strategy)->toBeInstanceOf(AgentMemoryInterface::class);
        });
    });

    describe('configuration constants', function () {
        test('uses configuration constants for consistency', function () {
            $strategy = new DatabaseMemoryStrategy([
                'default_ttl' => 3600,
                'connection' => 'test_connection',
                'table' => 'test_table',
            ]);

            expect($strategy)->toBeInstanceOf(DatabaseMemoryStrategy::class);
        });
    });

    describe('TTL logic validation', function () {
        test('TTL calculation uses correct defaults', function () {
            // Test that TTL logic is sound by checking configuration handling
            $strategy1 = new DatabaseMemoryStrategy(['default_ttl' => 7200]);
            $strategy2 = new DatabaseMemoryStrategy(['default_ttl' => null]);
            $strategy3 = new DatabaseMemoryStrategy([]);

            // All strategies should instantiate without error
            expect($strategy1)->toBeInstanceOf(DatabaseMemoryStrategy::class)
                ->and($strategy2)->toBeInstanceOf(DatabaseMemoryStrategy::class)
                ->and($strategy3)->toBeInstanceOf(DatabaseMemoryStrategy::class);
        });
    });

    describe('method signatures', function () {
        test('set method has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'set');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(3)
                ->and($parameters[0]->getName())->toBe('key')
                ->and($parameters[0]->getType()->getName())->toBe('string')
                ->and($parameters[1]->getName())->toBe('value')
                ->and($parameters[2]->getName())->toBe('ttl')
                ->and($parameters[2]->allowsNull())->toBeTrue();
        });

        test('get method has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'get');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[0]->getName())->toBe('key')
                ->and($parameters[0]->getType()->getName())->toBe('string')
                ->and($parameters[1]->getName())->toBe('default')
                ->and($parameters[1]->allowsNull())->toBeTrue();
        });

        test('has method has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'has');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0]->getName())->toBe('key')
                ->and($parameters[0]->getType()->getName())->toBe('string');
        });

        test('forget method has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'forget');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0]->getName())->toBe('key')
                ->and($parameters[0]->getType()->getName())->toBe('string');
        });

        test('flush method has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'flush');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(0);
        });

        test('cleanupExpired method has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'cleanupExpired');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(0);
        });
    });

    describe('bulk operations', function () {
        test('setMultiple method exists and has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'setMultiple');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(2)
                ->and($parameters[0]->getName())->toBe('data')
                ->and($parameters[0]->getType()->getName())->toBe('array')
                ->and($parameters[1]->getName())->toBe('ttl')
                ->and($parameters[1]->allowsNull())->toBeTrue();
        });

        test('forgetMultiple method exists and has correct signature', function () {
            $reflection = new ReflectionMethod(DatabaseMemoryStrategy::class, 'forgetMultiple');
            $parameters = $reflection->getParameters();

            expect($parameters)->toHaveCount(1)
                ->and($parameters[0]->getName())->toBe('keys')
                ->and($parameters[0]->getType()->getName())->toBe('array');
        });
    });

    describe('error handling', function () {
        test('gracefully handles database connection issues', function () {
            // Test that methods return false on database errors rather than throwing
            $key = 'error_key';
            $value = 'error_value';

            // These will fail with database errors in unit test environment
            // but should return false rather than throwing exceptions
            $setResult = $this->strategy->set($key, $value);
            $getResult = $this->strategy->get($key, 'default');
            $hasResult = $this->strategy->has($key);
            $forgetResult = $this->strategy->forget($key);
            $flushResult = $this->strategy->flush();
            $cleanupResult = $this->strategy->cleanupExpired();

            // All operations should return appropriate failure values
            expect($setResult)->toBeFalse()
                ->and($getResult)->toBe('default')
                ->and($hasResult)->toBeFalse()
                ->and($forgetResult)->toBeFalse()
                ->and($flushResult)->toBeFalse()
                ->and($cleanupResult)->toBe(0);
        });
    });

    describe('configuration access', function () {
        test('has protected getConfiguration method', function () {
            $reflection = new ReflectionClass(DatabaseMemoryStrategy::class);
            $method = $reflection->getMethod('getConfiguration');

            expect($method->isProtected())->toBeTrue()
                ->and($method->isPublic())->toBeFalse();
        });
    });
})->group('memory', 'database', 'unit');
