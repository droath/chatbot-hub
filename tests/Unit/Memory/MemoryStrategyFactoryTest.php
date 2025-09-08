<?php

declare(strict_types=1);

use Droath\ChatbotHub\Memory\MemoryStrategyFactory;
use Droath\ChatbotHub\Memory\Configuration\MemoryConfiguration;
use Droath\ChatbotHub\Memory\Contracts\MemoryStrategyInterface;

describe('MemoryStrategyFactory', function () {
    beforeEach(function () {
        $this->config = new MemoryConfiguration([
            'default' => 'session',
            'strategies' => [
                'session' => ['enabled' => true],
                'database' => ['enabled' => true],
                'null' => ['enabled' => false],
            ]
        ]);
        
        $this->factory = new MemoryStrategyFactory($this->config);
    });

    test('can be instantiated', function () {
        expect($this->factory)->toBeInstanceOf(MemoryStrategyFactory::class);
    });

    test('createDefault returns default strategy', function () {
        $strategy = $this->factory->createDefault();
        
        expect($strategy)->toBeInstanceOf(MemoryStrategyInterface::class);
        // We'll mock strategies in implementation tests since they don't exist yet
    });

    test('getAvailableStrategies returns only enabled strategies', function () {
        $strategies = $this->factory->getAvailableStrategies();
        
        expect($strategies)->toContain('session')
            ->and($strategies)->toContain('database')
            ->and($strategies)->not->toContain('null') // Disabled
            ->and($strategies)->toHaveCount(2);
    });

    test('hasStrategy checks availability correctly', function () {
        expect($this->factory->hasStrategy('session'))->toBeTrue()
            ->and($this->factory->hasStrategy('database'))->toBeTrue()
            ->and($this->factory->hasStrategy('null'))->toBeFalse() // Disabled
            ->and($this->factory->hasStrategy('invalid'))->toBeFalse(); // Invalid
    });

    test('throws exception for disabled strategy', function () {
        expect(fn () => $this->factory->create('null'))
            ->toThrow(\InvalidArgumentException::class, "Memory strategy 'null' is disabled");
    });

    test('throws exception for unknown strategy', function () {
        expect(fn () => $this->factory->create('unknown'))
            ->toThrow(\InvalidArgumentException::class, 'Invalid memory strategy');
    });
});

describe('MemoryStrategyFactory Configuration Integration', function () {
    test('uses configuration for strategy creation', function () {
        $config = new MemoryConfiguration([
            'default' => 'database',
            'strategies' => [
                'database' => [
                    'enabled' => true,
                    'table' => 'custom_memory_table'
                ]
            ]
        ]);
        
        $factory = new MemoryStrategyFactory($config);
        
        expect($factory->hasStrategy('database'))->toBeTrue();
        expect($config->getDefaultStrategy())->toBe('database');
    });

    test('respects enabled/disabled strategy settings', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'session' => ['enabled' => false],
                'database' => ['enabled' => true],
            ]
        ]);
        
        $factory = new MemoryStrategyFactory($config);
        
        expect($factory->hasStrategy('session'))->toBeFalse()
            ->and($factory->hasStrategy('database'))->toBeTrue();
    });

    test('handles empty configuration gracefully', function () {
        $config = new MemoryConfiguration([]);
        $factory = new MemoryStrategyFactory($config);
        
        // Should still work with defaults
        expect($factory->hasStrategy('session'))->toBeTrue(); // Default strategy
        expect($factory->getAvailableStrategies())->toBeArray();
    });
});