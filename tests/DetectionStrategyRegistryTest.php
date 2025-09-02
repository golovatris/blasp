<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Registries\DetectionStrategyRegistry;
use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use InvalidArgumentException;

class DetectionStrategyRegistryTest extends TestCase
{
    private DetectionStrategyRegistry $registry;
    private DetectionStrategyInterface $mockStrategy;

    public function setUp(): void
    {
        parent::setUp();
        $this->registry = new DetectionStrategyRegistry();
        
        // Create a mock strategy
        $this->mockStrategy = $this->createMock(DetectionStrategyInterface::class);
        $this->mockStrategy->method('getName')->willReturn('test_strategy');
        $this->mockStrategy->method('getPriority')->willReturn(100);
        $this->mockStrategy->method('canHandle')->willReturn(true);
        $this->mockStrategy->method('detect')->willReturn([]);
    }

    public function test_can_register_strategy()
    {
        $this->registry->register('test', $this->mockStrategy);
        
        $this->assertTrue($this->registry->has('test'));
        $this->assertSame($this->mockStrategy, $this->registry->get('test'));
    }

    public function test_register_throws_exception_for_invalid_type()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Item must be an instance of DetectionStrategyInterface');
        
        $this->registry->register('invalid', 'not_a_strategy');
    }

    public function test_get_throws_exception_for_unknown_strategy()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No detection strategy registered with key: unknown');
        
        $this->registry->get('unknown');
    }

    public function test_has_returns_false_for_unknown_strategy()
    {
        $this->assertFalse($this->registry->has('unknown'));
    }

    public function test_all_returns_all_strategies()
    {
        $strategy1 = $this->createMockStrategy('strategy1', 100);
        $strategy2 = $this->createMockStrategy('strategy2', 200);
        
        $this->registry->register('first', $strategy1);
        $this->registry->register('second', $strategy2);
        
        $all = $this->registry->all();
        $this->assertCount(2, $all);
        $this->assertSame($strategy1, $all['first']);
        $this->assertSame($strategy2, $all['second']);
    }

    public function test_get_all_by_priority_sorts_correctly()
    {
        $lowPriority = $this->createMockStrategy('low', 50);
        $highPriority = $this->createMockStrategy('high', 200);
        $mediumPriority = $this->createMockStrategy('medium', 100);
        
        $this->registry->register('low', $lowPriority);
        $this->registry->register('high', $highPriority);
        $this->registry->register('medium', $mediumPriority);
        
        $sorted = $this->registry->getAllByPriority();
        
        $this->assertCount(3, $sorted);
        $this->assertSame($highPriority, $sorted[0]);
        $this->assertSame($mediumPriority, $sorted[1]);
        $this->assertSame($lowPriority, $sorted[2]);
    }

    public function test_get_applicable_strategies_filters_correctly()
    {
        $canHandle = $this->createMock(DetectionStrategyInterface::class);
        $canHandle->method('getName')->willReturn('can_handle');
        $canHandle->method('getPriority')->willReturn(100);
        $canHandle->method('canHandle')->willReturn(true);
        $canHandle->method('detect')->willReturn([]);
        
        $cannotHandle = $this->createMock(DetectionStrategyInterface::class);
        $cannotHandle->method('getName')->willReturn('cannot_handle');
        $cannotHandle->method('getPriority')->willReturn(200);
        $cannotHandle->method('canHandle')->willReturn(false);
        $cannotHandle->method('detect')->willReturn([]);
        
        $this->registry->register('can', $canHandle);
        $this->registry->register('cannot', $cannotHandle);
        
        $applicable = $this->registry->getApplicableStrategies('test text', ['domain' => 'gaming']);
        
        $this->assertCount(1, $applicable);
        $this->assertSame($canHandle, $applicable[0]);
    }

    public function test_remove_strategy()
    {
        $this->registry->register('test', $this->mockStrategy);
        $this->assertTrue($this->registry->has('test'));
        
        $this->registry->remove('test');
        $this->assertFalse($this->registry->has('test'));
    }

    public function test_case_insensitive_keys()
    {
        $this->registry->register('TEST', $this->mockStrategy);
        
        $this->assertTrue($this->registry->has('test'));
        $this->assertTrue($this->registry->has('TEST'));
        $this->assertSame($this->mockStrategy, $this->registry->get('test'));
        $this->assertSame($this->mockStrategy, $this->registry->get('TEST'));
    }

    private function createMockStrategy(string $name, int $priority): DetectionStrategyInterface
    {
        $mock = $this->createMock(DetectionStrategyInterface::class);
        $mock->method('getName')->willReturn($name);
        $mock->method('getPriority')->willReturn($priority);
        $mock->method('canHandle')->willReturn(true);
        $mock->method('detect')->willReturn([]);
        
        return $mock;
    }
}