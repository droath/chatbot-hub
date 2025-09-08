<?php

declare(strict_types=1);

use Droath\ChatbotHub\Memory\Configuration\MemoryConfiguration;

describe('MemoryConfiguration', function () {
    test('can be instantiated with empty config', function () {
        $config = new MemoryConfiguration();
        
        expect($config)->toBeInstanceOf(MemoryConfiguration::class);
        expect($config->getDefaultStrategy())->toBe(MemoryConfiguration::DEFAULT_STRATEGY);
        expect($config->getDefaultTtl())->toBe(MemoryConfiguration::DEFAULT_TTL);
    });

    test('can be instantiated with custom config', function () {
        $customConfig = [
            'default' => 'database',
            'default_ttl' => 7200,
            'strategies' => [
                'session' => ['enabled' => true],
                'database' => ['enabled' => false],
            ]
        ];

        $config = new MemoryConfiguration($customConfig);
        
        expect($config->getDefaultStrategy())->toBe('database');
        expect($config->getDefaultTtl())->toBe(7200);
        expect($config->isStrategyEnabled('session'))->toBeTrue();
        expect($config->isStrategyEnabled('database'))->toBeFalse();
    });

    test('validates strategy names', function () {
        expect(fn () => new MemoryConfiguration(['default' => 'invalid_strategy']))
            ->toThrow(InvalidArgumentException::class, 'Invalid memory strategy');
    });

    test('validates default_ttl', function () {
        expect(fn () => new MemoryConfiguration(['default_ttl' => -1]))
            ->toThrow(InvalidArgumentException::class, 'default_ttl must be a non-negative integer');
            
        expect(fn () => new MemoryConfiguration(['default_ttl' => 'invalid']))
            ->toThrow(InvalidArgumentException::class, 'default_ttl must be a non-negative integer');
    });

    test('getStrategyConfig returns correct configuration', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'session' => [
                    'enabled' => true,
                    'prefix' => 'custom_prefix'
                ]
            ]
        ]);

        $sessionConfig = $config->getStrategyConfig('session');
        expect($sessionConfig)->toHaveKey('enabled')
            ->and($sessionConfig['enabled'])->toBeTrue()
            ->and($sessionConfig)->toHaveKey('prefix')
            ->and($sessionConfig['prefix'])->toBe('custom_prefix');
    });

    test('getStrategyConfig throws for invalid strategy', function () {
        $config = new MemoryConfiguration();
        
        expect(fn () => $config->getStrategyConfig('invalid'))
            ->toThrow(InvalidArgumentException::class, 'Invalid memory strategy');
    });

    test('isStrategyEnabled handles missing strategy gracefully', function () {
        $config = new MemoryConfiguration();
        
        // Valid strategy but not configured should default to enabled
        expect($config->isStrategyEnabled('session'))->toBeTrue();
    });

    test('getAvailableStrategies returns configured strategies', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'session' => ['enabled' => true],
                'database' => ['enabled' => false],
            ]
        ]);

        $strategies = $config->getAvailableStrategies();
        expect($strategies)->toContain('session')
            ->and($strategies)->toContain('database')
            ->and($strategies)->toHaveCount(2);
    });

    test('toArray returns complete configuration', function () {
        $inputConfig = [
            'default' => 'database',
            'default_ttl' => 7200,
            'strategies' => [
                'session' => ['enabled' => true]
            ]
        ];

        $config = new MemoryConfiguration($inputConfig);
        $outputConfig = $config->toArray();
        
        expect($outputConfig)->toHaveKey('default')
            ->and($outputConfig)->toHaveKey('default_ttl')
            ->and($outputConfig)->toHaveKey('strategies')
            ->and($outputConfig['default'])->toBe('database')
            ->and($outputConfig['default_ttl'])->toBe(7200);
    });
});

describe('MemoryConfiguration Session Strategy Validation', function () {
    test('validates session strategy config with defaults', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'session' => []
            ]
        ]);

        $sessionConfig = $config->getStrategyConfig('session');
        expect($sessionConfig['enabled'])->toBeTrue()
            ->and($sessionConfig['prefix'])->toBe('agent_memory');
    });

    test('validates session strategy config with custom values', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'session' => [
                    'enabled' => false,
                    'prefix' => 'custom_memory'
                ]
            ]
        ]);

        $sessionConfig = $config->getStrategyConfig('session');
        expect($sessionConfig['enabled'])->toBeFalse()
            ->and($sessionConfig['prefix'])->toBe('custom_memory');
    });

    test('throws on invalid session config', function () {
        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'session' => ['prefix' => 123]
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Session strategy prefix must be a string');

        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'session' => ['enabled' => 'yes']
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Session strategy enabled must be a boolean');
    });
});

describe('MemoryConfiguration Database Strategy Validation', function () {
    test('validates database strategy config with defaults', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'database' => []
            ]
        ]);

        $dbConfig = $config->getStrategyConfig('database');
        expect($dbConfig['enabled'])->toBeTrue()
            ->and($dbConfig['connection'])->toBeNull()
            ->and($dbConfig['table'])->toBe('agent_memory')
            ->and($dbConfig['cleanup_probability'])->toBe(100);
    });

    test('validates database strategy config with custom values', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'database' => [
                    'enabled' => false,
                    'connection' => 'memory_db',
                    'table' => 'custom_memory',
                    'cleanup_probability' => 50
                ]
            ]
        ]);

        $dbConfig = $config->getStrategyConfig('database');
        expect($dbConfig['enabled'])->toBeFalse()
            ->and($dbConfig['connection'])->toBe('memory_db')
            ->and($dbConfig['table'])->toBe('custom_memory')
            ->and($dbConfig['cleanup_probability'])->toBe(50);
    });

    test('throws on invalid database config', function () {
        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'database' => ['connection' => 123]
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Database strategy connection must be a string or null');

        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'database' => ['table' => 123]
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Database strategy table must be a string');

        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'database' => ['cleanup_probability' => 150]
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Database strategy cleanup_probability must be an integer between 0 and 100');

        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'database' => ['enabled' => 'yes']
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Database strategy enabled must be a boolean');
    });
});

describe('MemoryConfiguration Null Strategy Validation', function () {
    test('validates null strategy config', function () {
        $config = new MemoryConfiguration([
            'strategies' => [
                'null' => []
            ]
        ]);

        $nullConfig = $config->getStrategyConfig('null');
        expect($nullConfig['enabled'])->toBeTrue();
    });

    test('throws on invalid null config', function () {
        expect(fn () => new MemoryConfiguration([
            'strategies' => [
                'null' => ['enabled' => 'yes']
            ]
        ]))->toThrow(InvalidArgumentException::class, 'Null strategy enabled must be a boolean');
    });
});

describe('MemoryConfiguration Constants', function () {
    test('has correct constants', function () {
        expect(MemoryConfiguration::VALID_STRATEGIES)->toBe(['session', 'database', 'null'])
            ->and(MemoryConfiguration::DEFAULT_TTL)->toBe(3600)
            ->and(MemoryConfiguration::DEFAULT_STRATEGY)->toBe('session');
    });
});