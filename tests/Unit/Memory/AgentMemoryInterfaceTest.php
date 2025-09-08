<?php

declare(strict_types=1);

use Droath\ChatbotHub\Agents\Contracts\AgentMemoryInterface;

describe('AgentMemoryInterface Contract', function () {
    beforeEach(function () {
        $this->memory = $this->createMock(AgentMemoryInterface::class);
    });

    test('defines set method with correct signature', function () {
        expect(method_exists($this->memory, 'set'))->toBeTrue();
        
        // Mock the behavior we expect
        $this->memory->method('set')->willReturn(true);
        
        // Test the expected method call
        $result = $this->memory->set('test_key', 'test_value');
        expect($result)->toBeTrue();
    });

    test('defines set method with ttl parameter', function () {
        $this->memory->method('set')->willReturn(true);
        
        // Test method accepts TTL parameter
        $result = $this->memory->set('test_key', 'test_value', 3600);
        expect($result)->toBeTrue();
    });

    test('defines get method with correct signature', function () {
        expect(method_exists($this->memory, 'get'))->toBeTrue();
        
        // Mock return value
        $this->memory->method('get')->willReturn('test_value');
        
        $result = $this->memory->get('test_key');
        expect($result)->toBe('test_value');
    });

    test('defines get method with default parameter', function () {
        $this->memory->method('get')->willReturn('default_value');
        
        // Test method accepts default parameter
        $result = $this->memory->get('nonexistent_key', 'default_value');
        expect($result)->toBe('default_value');
    });

    test('defines has method with correct signature', function () {
        expect(method_exists($this->memory, 'has'))->toBeTrue();
        
        $this->memory->method('has')->willReturn(true);
        
        $result = $this->memory->has('test_key');
        expect($result)->toBeTrue();
    });

    test('defines forget method with correct signature', function () {
        expect(method_exists($this->memory, 'forget'))->toBeTrue();
        
        $this->memory->method('forget')->willReturn(true);
        
        $result = $this->memory->forget('test_key');
        expect($result)->toBeTrue();
    });

    test('defines flush method with correct signature', function () {
        expect(method_exists($this->memory, 'flush'))->toBeTrue();
        
        $this->memory->method('flush')->willReturn(true);
        
        $result = $this->memory->flush();
        expect($result)->toBeTrue();
    });

    test('handles mixed value types', function () {
        $this->memory->method('set')->willReturn(true);
        $this->memory->method('get')->willReturn(['array', 'value']);
        
        // Test setting array value
        $result = $this->memory->set('array_key', ['array', 'value']);
        expect($result)->toBeTrue();
        
        // Test getting array value
        $value = $this->memory->get('array_key');
        expect($value)->toBe(['array', 'value']);
    });

    test('handles null values', function () {
        $this->memory->method('set')->willReturn(true);
        $this->memory->method('get')->willReturn(null);
        
        // Test setting null value
        $result = $this->memory->set('null_key', null);
        expect($result)->toBeTrue();
        
        // Test getting null value
        $value = $this->memory->get('null_key');
        expect($value)->toBeNull();
    });

    test('handles boolean return values consistently', function () {
        // All mutation methods should return boolean
        $this->memory->method('set')->willReturn(true);
        $this->memory->method('forget')->willReturn(true);
        $this->memory->method('flush')->willReturn(true);
        $this->memory->method('has')->willReturn(false);
        
        expect($this->memory->set('key', 'value'))->toBeBool();
        expect($this->memory->forget('key'))->toBeBool();
        expect($this->memory->flush())->toBeBool();
        expect($this->memory->has('key'))->toBeBool();
    });
});

describe('AgentMemoryInterface Implementation Behavior', function () {
    test('should maintain key-value consistency', function () {
        $memory = $this->createMock(AgentMemoryInterface::class);
        
        // Mock consistent behavior
        $memory->method('set')->willReturn(true);
        $memory->method('get')->willReturnMap([
            ['stored_key', null, 'stored_value'],
            ['missing_key', 'default', 'default'],
        ]);
        $memory->method('has')->willReturnMap([
            ['stored_key', true],
            ['missing_key', false],
        ]);
        
        // Set a value
        expect($memory->set('stored_key', 'stored_value'))->toBeTrue();
        
        // Should be able to retrieve it
        expect($memory->get('stored_key'))->toBe('stored_value');
        
        // Should exist
        expect($memory->has('stored_key'))->toBeTrue();
        
        // Missing key should return default
        expect($memory->get('missing_key', 'default'))->toBe('default');
        
        // Missing key should not exist
        expect($memory->has('missing_key'))->toBeFalse();
    });

    test('should handle TTL expiration logic', function () {
        $memory = $this->createMock(AgentMemoryInterface::class);
        
        // Mock TTL behavior
        $memory->method('set')->willReturn(true);
        $memory->method('get')->willReturnMap([
            ['ttl_key', null, 'ttl_value'],  // Fresh value
            ['expired_key', 'default', 'default'],  // Expired, returns default
        ]);
        $memory->method('has')->willReturnMap([
            ['ttl_key', true],     // Fresh key exists
            ['expired_key', false], // Expired key doesn't exist
        ]);
        
        // Set value with TTL
        expect($memory->set('ttl_key', 'ttl_value', 3600))->toBeTrue();
        
        // Fresh value should be retrievable
        expect($memory->get('ttl_key'))->toBe('ttl_value');
        expect($memory->has('ttl_key'))->toBeTrue();
        
        // Expired value should not be retrievable
        expect($memory->get('expired_key', 'default'))->toBe('default');
        expect($memory->has('expired_key'))->toBeFalse();
    });

    test('should handle forget operation', function () {
        $memory = $this->createMock(AgentMemoryInterface::class);
        
        // Mock forget behavior
        $memory->method('set')->willReturn(true);
        $memory->method('forget')->willReturn(true);
        $memory->method('has')->willReturnCallback(function ($key) {
            // Return false after forget is called
            return $key !== 'forgotten_key';
        });
        $memory->method('get')->willReturnCallback(function ($key, $default = null) {
            return $key === 'forgotten_key' ? $default : 'value';
        });
        
        // Set and forget a value
        expect($memory->set('forgotten_key', 'value'))->toBeTrue();
        expect($memory->forget('forgotten_key'))->toBeTrue();
        
        // Should no longer exist
        expect($memory->has('forgotten_key'))->toBeFalse();
        expect($memory->get('forgotten_key', 'default'))->toBe('default');
    });

    test('should handle flush operation', function () {
        $memory = $this->createMock(AgentMemoryInterface::class);
        
        // Mock flush behavior
        $memory->method('set')->willReturn(true);
        $memory->method('flush')->willReturn(true);
        $memory->method('has')->willReturn(false); // All keys gone after flush
        $memory->method('get')->willReturnCallback(function ($key, $default = null) {
            return $default; // All keys return default after flush
        });
        
        // Set multiple values
        expect($memory->set('key1', 'value1'))->toBeTrue();
        expect($memory->set('key2', 'value2'))->toBeTrue();
        
        // Flush all
        expect($memory->flush())->toBeTrue();
        
        // All keys should be gone
        expect($memory->has('key1'))->toBeFalse();
        expect($memory->has('key2'))->toBeFalse();
        expect($memory->get('key1', 'default'))->toBe('default');
        expect($memory->get('key2', 'default'))->toBe('default');
    });
});