<?php

declare(strict_types=1);

use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;
use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;

describe('MemoryStrategyInterface Contract', function () {
    beforeEach(function () {
        $this->strategy = $this->createMock(MemoryStrategyInterface::class);
    });

    test('extends AgentMemoryInterface', function () {
        expect($this->strategy)->toBeInstanceOf(AgentMemoryInterface::class);
    });

    test('defines getStrategyName method', function () {
        expect(method_exists($this->strategy, 'getStrategyName'))->toBeTrue();
        
        $this->strategy->method('getStrategyName')->willReturn('test_strategy');
        
        $result = $this->strategy->getStrategyName();
        expect($result)->toBe('test_strategy')->and($result)->toBeString();
    });

    test('defines isHealthy method', function () {
        expect(method_exists($this->strategy, 'isHealthy'))->toBeTrue();
        
        $this->strategy->method('isHealthy')->willReturn(true);
        
        $result = $this->strategy->isHealthy();
        expect($result)->toBeTrue()->and($result)->toBeBool();
    });

    test('defines getConfiguration method', function () {
        expect(method_exists($this->strategy, 'getConfiguration'))->toBeTrue();
        
        $config = ['timeout' => 30, 'enabled' => true];
        $this->strategy->method('getConfiguration')->willReturn($config);
        
        $result = $this->strategy->getConfiguration();
        expect($result)->toBe($config)->and($result)->toBeArray();
    });

    test('defines cleanupExpired method', function () {
        expect(method_exists($this->strategy, 'cleanupExpired'))->toBeTrue();
        
        $this->strategy->method('cleanupExpired')->willReturn(5);
        
        $result = $this->strategy->cleanupExpired();
        expect($result)->toBe(5)->and($result)->toBeInt();
    });

    test('inherits all AgentMemoryInterface methods', function () {
        // Should have all base memory interface methods
        expect(method_exists($this->strategy, 'set'))->toBeTrue()
            ->and(method_exists($this->strategy, 'get'))->toBeTrue()
            ->and(method_exists($this->strategy, 'has'))->toBeTrue()
            ->and(method_exists($this->strategy, 'forget'))->toBeTrue()
            ->and(method_exists($this->strategy, 'flush'))->toBeTrue();
    });
});

describe('MemoryStrategyInterface Expected Behavior', function () {
    test('should provide strategy identification', function () {
        $strategy = $this->createMock(MemoryStrategyInterface::class);
        
        $strategy->method('getStrategyName')->willReturn('session');
        $strategy->method('getConfiguration')->willReturn([
            'driver' => 'file',
            'lifetime' => 120
        ]);
        
        expect($strategy->getStrategyName())->toBe('session');
        expect($strategy->getConfiguration())->toHaveKeys(['driver', 'lifetime']);
    });

    test('should provide health checking', function () {
        $strategy = $this->createMock(MemoryStrategyInterface::class);
        
        // Mock healthy strategy
        $strategy->method('isHealthy')->willReturn(true);
        expect($strategy->isHealthy())->toBeTrue();
        
        // Reset mock for unhealthy strategy
        $unhealthyStrategy = $this->createMock(MemoryStrategyInterface::class);
        $unhealthyStrategy->method('isHealthy')->willReturn(false);
        expect($unhealthyStrategy->isHealthy())->toBeFalse();
    });

    test('should provide cleanup functionality', function () {
        $strategy = $this->createMock(MemoryStrategyInterface::class);
        
        // Mock cleanup operation
        $strategy->method('cleanupExpired')->willReturn(3);
        
        $cleaned = $strategy->cleanupExpired();
        expect($cleaned)->toBeInt()->and($cleaned)->toBeGreaterThanOrEqual(0);
    });

    test('should maintain memory interface compatibility', function () {
        $strategy = $this->createMock(MemoryStrategyInterface::class);
        
        // Mock memory operations
        $strategy->method('set')->willReturn(true);
        $strategy->method('get')->willReturn('test_value');
        $strategy->method('has')->willReturn(true);
        $strategy->method('forget')->willReturn(true);
        $strategy->method('flush')->willReturn(true);
        
        // Should work as an AgentMemoryInterface
        expect($strategy->set('key', 'value'))->toBeTrue();
        expect($strategy->get('key'))->toBe('test_value');
        expect($strategy->has('key'))->toBeTrue();
        expect($strategy->forget('key'))->toBeTrue();
        expect($strategy->flush())->toBeTrue();
    });

    test('should provide different strategy configurations', function () {
        $sessionStrategy = $this->createMock(MemoryStrategyInterface::class);
        $dbStrategy = $this->createMock(MemoryStrategyInterface::class);
        
        $sessionStrategy->method('getStrategyName')->willReturn('session');
        $sessionStrategy->method('getConfiguration')->willReturn([
            'driver' => 'file',
            'lifetime' => 120,
            'cookie' => 'laravel_session'
        ]);
        
        $dbStrategy->method('getStrategyName')->willReturn('database');
        $dbStrategy->method('getConfiguration')->willReturn([
            'connection' => 'default',
            'table' => 'agent_memory',
            'cleanup_probability' => 100
        ]);
        
        expect($sessionStrategy->getStrategyName())->not->toBe($dbStrategy->getStrategyName());
        expect($sessionStrategy->getConfiguration())->not->toBe($dbStrategy->getConfiguration());
        
        // Each should have strategy-specific config keys
        expect($sessionStrategy->getConfiguration())->toHaveKey('lifetime');
        expect($dbStrategy->getConfiguration())->toHaveKey('table');
    });
});