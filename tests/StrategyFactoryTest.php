<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Factories\StrategyFactory;
use Blaspsoft\Blasp\Strategies\DefaultDetectionStrategy;
use Blaspsoft\Blasp\Strategies\GamingDetectionStrategy;
use Blaspsoft\Blasp\Strategies\SocialMediaDetectionStrategy;
use Blaspsoft\Blasp\Strategies\WorkplaceDetectionStrategy;
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

    public function test_create_gaming_strategy()
    {
        $strategy = StrategyFactory::create('gaming');
        
        $this->assertInstanceOf(GamingDetectionStrategy::class, $strategy);
        $this->assertEquals('gaming', $strategy->getName());
    }

    public function test_create_social_media_strategy()
    {
        $strategy = StrategyFactory::create('social_media');
        
        $this->assertInstanceOf(SocialMediaDetectionStrategy::class, $strategy);
        $this->assertEquals('social_media', $strategy->getName());
    }

    public function test_create_workplace_strategy()
    {
        $strategy = StrategyFactory::create('workplace');
        
        $this->assertInstanceOf(WorkplaceDetectionStrategy::class, $strategy);
        $this->assertEquals('workplace', $strategy->getName());
    }

    public function test_create_is_case_insensitive()
    {
        $strategy1 = StrategyFactory::create('GAMING');
        $strategy2 = StrategyFactory::create('Gaming');
        $strategy3 = StrategyFactory::create('gaming');
        
        $this->assertInstanceOf(GamingDetectionStrategy::class, $strategy1);
        $this->assertInstanceOf(GamingDetectionStrategy::class, $strategy2);
        $this->assertInstanceOf(GamingDetectionStrategy::class, $strategy3);
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
        $this->assertContains('gaming', $strategies);
        $this->assertContains('social_media', $strategies);
        $this->assertContains('workplace', $strategies);
        $this->assertCount(4, $strategies);
    }

    public function test_create_multiple_strategies()
    {
        $strategies = StrategyFactory::createMultiple(['default', 'gaming', 'workplace']);
        
        $this->assertCount(3, $strategies);
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategies[0]);
        $this->assertInstanceOf(GamingDetectionStrategy::class, $strategies[1]);
        $this->assertInstanceOf(WorkplaceDetectionStrategy::class, $strategies[2]);
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

    public function test_create_for_gaming_context()
    {
        $strategies = StrategyFactory::createForContext(['domain' => 'gaming']);
        
        $this->assertCount(2, $strategies);
        
        $strategyNames = array_map(fn($s) => $s->getName(), $strategies);
        $this->assertContains('default', $strategyNames);
        $this->assertContains('gaming', $strategyNames);
    }

    public function test_create_for_social_media_context()
    {
        $strategies = StrategyFactory::createForContext(['platform' => 'twitter']);
        
        $this->assertCount(2, $strategies);
        
        $strategyNames = array_map(fn($s) => $s->getName(), $strategies);
        $this->assertContains('default', $strategyNames);
        $this->assertContains('social_media', $strategyNames);
    }

    public function test_create_for_workplace_context()
    {
        $strategies = StrategyFactory::createForContext(['environment' => 'workplace']);
        
        $this->assertCount(2, $strategies);
        
        $strategyNames = array_map(fn($s) => $s->getName(), $strategies);
        $this->assertContains('default', $strategyNames);
        $this->assertContains('workplace', $strategyNames);
    }

    public function test_create_for_multiple_contexts()
    {
        $strategies = StrategyFactory::createForContext([
            'domain' => 'gaming',
            'platform' => 'twitter'
        ]);
        
        $this->assertCount(3, $strategies);
        
        $strategyNames = array_map(fn($s) => $s->getName(), $strategies);
        $this->assertContains('default', $strategyNames);
        $this->assertContains('gaming', $strategyNames);
        $this->assertContains('social_media', $strategyNames);
    }

    public function test_create_for_context_removes_duplicates()
    {
        $strategies = StrategyFactory::createForContext([
            'platform' => 'facebook', // Should create social_media
            'tags' => ['social_media'] // Also creates social_media (but shouldn't duplicate)
        ]);
        
        // Should have default + social_media (no duplicates)
        $strategyNames = array_map(fn($s) => $s->getName(), $strategies);
        $uniqueNames = array_unique($strategyNames);
        
        $this->assertEquals(count($strategyNames), count($uniqueNames));
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