<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Factories\StrategyFactory;
use Blaspsoft\Blasp\Strategies\DefaultDetectionStrategy;
use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use InvalidArgumentException;

class StrategyFactoryTest extends TestCase
{
    public function test_create_default_strategy()
    {
        $strategy = StrategyFactory::create('default');
        
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategy);
        $this->assertEquals('default', $strategy->getName());
    }


    public function test_create_is_case_insensitive()
    {
        $strategy1 = StrategyFactory::create('DEFAULT');
        $strategy2 = StrategyFactory::create('Default');
        $strategy3 = StrategyFactory::create('default');
        
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategy1);
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategy2);
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategy3);
    }

    public function test_create_throws_exception_for_unknown_strategy()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown strategy: unknown');
        
        StrategyFactory::create('unknown');
    }

    public function test_get_available_strategies()
    {
        $strategies = StrategyFactory::getAvailableStrategies();
        
        $this->assertIsArray($strategies);
        $this->assertContains('default', $strategies);
        $this->assertCount(1, $strategies);
    }

    public function test_create_multiple_strategies()
    {
        $strategies = StrategyFactory::createMultiple(['default']);
        
        $this->assertCount(1, $strategies);
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategies[0]);
    }

    public function test_create_multiple_throws_exception_for_unknown_strategy()
    {
        $this->expectException(InvalidArgumentException::class);
        
        StrategyFactory::createMultiple(['default', 'unknown']);
    }

    public function test_register_custom_strategy()
    {
        // Create a mock strategy class
        $mockStrategyClass = new class implements DetectionStrategyInterface {
            public function getName(): string { return 'custom'; }
            public function getPriority(): int { return 100; }
            public function detect(string $text, array $profanityExpressions, array $falsePositives): array { return []; }
            public function canHandle(string $text, array $context = []): bool { return true; }
        };
        
        StrategyFactory::registerStrategy('custom', get_class($mockStrategyClass));
        
        $this->assertContains('custom', StrategyFactory::getAvailableStrategies());
        
        $strategy = StrategyFactory::create('custom');
        $this->assertEquals('custom', $strategy->getName());
    }

    public function test_register_strategy_throws_exception_for_non_existent_class()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy class does not exist: NonExistentClass');
        
        StrategyFactory::registerStrategy('invalid', 'NonExistentClass');
    }

    public function test_register_strategy_throws_exception_for_invalid_interface()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy class must implement DetectionStrategyInterface');
        
        StrategyFactory::registerStrategy('invalid', \stdClass::class);
    }

    public function test_create_for_context_returns_default()
    {
        // Test various contexts - all should return only default now
        $strategies1 = StrategyFactory::createForContext(['domain' => 'gaming']);
        $strategies2 = StrategyFactory::createForContext(['platform' => 'twitter']);
        $strategies3 = StrategyFactory::createForContext(['environment' => 'workplace']);
        
        $this->assertCount(1, $strategies1);
        $this->assertCount(1, $strategies2);
        $this->assertCount(1, $strategies3);
        
        $this->assertEquals('default', $strategies1[0]->getName());
        $this->assertEquals('default', $strategies2[0]->getName());
        $this->assertEquals('default', $strategies3[0]->getName());
    }

    public function test_create_for_unknown_context_returns_only_default()
    {
        $strategies = StrategyFactory::createForContext(['unknown' => 'context']);
        
        $this->assertCount(1, $strategies);
        $this->assertEquals('default', $strategies[0]->getName());
    }

    public function test_create_for_empty_context_returns_only_default()
    {
        $strategies = StrategyFactory::createForContext([]);
        
        $this->assertCount(1, $strategies);
        $this->assertEquals('default', $strategies[0]->getName());
    }

    public function test_all_created_strategies_implement_interface()
    {
        $strategyNames = StrategyFactory::getAvailableStrategies();
        
        foreach ($strategyNames as $strategyName) {
            $strategy = StrategyFactory::create($strategyName);
            $this->assertInstanceOf(DetectionStrategyInterface::class, $strategy);
        }
    }

    public function test_all_created_strategies_have_consistent_properties()
    {
        $strategyNames = StrategyFactory::getAvailableStrategies();
        
        foreach ($strategyNames as $strategyName) {
            $strategy = StrategyFactory::create($strategyName);
            
            $this->assertIsString($strategy->getName());
            $this->assertIsInt($strategy->getPriority());
            $this->assertIsBool($strategy->canHandle('test text'));
            $this->assertIsArray($strategy->detect('test', [], []));
        }
    }
}