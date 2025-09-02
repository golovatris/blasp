<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Plugins\PluginManager;
use Blaspsoft\Blasp\Registries\DetectionStrategyRegistry;
use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use Blaspsoft\Blasp\Strategies\DefaultDetectionStrategy;

class PluginManagerTest extends TestCase
{
    private PluginManager $pluginManager;
    private DetectionStrategyRegistry $registry;

    public function setUp(): void
    {
        parent::setUp();
        $this->registry = new DetectionStrategyRegistry();
        $this->pluginManager = new PluginManager($this->registry);
    }

    public function test_plugin_manager_registers_default_strategy()
    {
        $strategies = $this->pluginManager->getAllStrategies();
        
        $this->assertCount(1, $strategies);
        $this->assertInstanceOf(DefaultDetectionStrategy::class, $strategies[0]);
        $this->assertEquals('default', $strategies[0]->getName());
    }

    public function test_can_register_custom_strategy()
    {
        $customStrategy = $this->createMockStrategy('custom', 150);
        
        $this->pluginManager->registerStrategy($customStrategy);
        
        $this->assertTrue($this->pluginManager->hasStrategy('custom'));
        $this->assertSame($customStrategy, $this->pluginManager->getStrategy('custom'));
    }

    public function test_can_remove_strategy()
    {
        $customStrategy = $this->createMockStrategy('custom', 150);
        $this->pluginManager->registerStrategy($customStrategy);
        
        $this->assertTrue($this->pluginManager->hasStrategy('custom'));
        
        $this->pluginManager->removeStrategy('custom');
        
        $this->assertFalse($this->pluginManager->hasStrategy('custom'));
    }

    public function test_get_all_strategies_returns_by_priority()
    {
        $highPriorityStrategy = $this->createMockStrategy('high', 200);
        $lowPriorityStrategy = $this->createMockStrategy('low', 50);
        
        $this->pluginManager->registerStrategy($lowPriorityStrategy);
        $this->pluginManager->registerStrategy($highPriorityStrategy);
        
        $strategies = $this->pluginManager->getAllStrategies();
        
        // Should be ordered by priority: high (200), default (100), low (50)
        $this->assertCount(3, $strategies);
        $this->assertEquals('high', $strategies[0]->getName());
        $this->assertEquals('default', $strategies[1]->getName());
        $this->assertEquals('low', $strategies[2]->getName());
    }

    public function test_get_applicable_strategies_filters_by_context()
    {
        $gamingStrategy = $this->createMockStrategy('gaming', 120);
        $gamingStrategy->method('canHandle')
            ->willReturnCallback(function ($text, $context) {
                return isset($context['domain']) && $context['domain'] === 'gaming';
            });
        
        $this->pluginManager->registerStrategy($gamingStrategy);
        
        // Test with gaming context
        $gamingApplicable = $this->pluginManager->getApplicableStrategies(
            'test text',
            ['domain' => 'gaming']
        );
        
        $this->assertCount(2, $gamingApplicable); // gaming + default
        $this->assertEquals('gaming', $gamingApplicable[0]->getName());
        $this->assertEquals('default', $gamingApplicable[1]->getName());
        
        // Test without gaming context
        $nonGamingApplicable = $this->pluginManager->getApplicableStrategies(
            'office meeting',
            ['domain' => 'workplace']
        );
        
        // Should have at least default
        $nonGamingNames = array_map(fn($s) => $s->getName(), $nonGamingApplicable);
        $this->assertContains('default', $nonGamingNames);
    }

    public function test_detect_profanities_combines_results_from_multiple_strategies()
    {
        // Create a mock strategy that finds specific matches
        $customStrategy = $this->createMock(DetectionStrategyInterface::class);
        $customStrategy->method('getName')->willReturn('custom');
        $customStrategy->method('getPriority')->willReturn(150);
        $customStrategy->method('canHandle')->willReturn(true);
        $customStrategy->method('detect')->willReturn([
            [
                'profanity' => 'custom_bad',
                'match' => 'custom_bad',
                'start' => 10,
                'length' => 10,
                'full_word' => 'custom_bad'
            ]
        ]);
        
        $this->pluginManager->registerStrategy($customStrategy);
        
        $results = $this->pluginManager->detectProfanities(
            'This has custom_bad word',
            ['custom_bad' => '/custom_bad/i'],
            []
        );
        
        // Should get results from the custom strategy
        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, $results);
        
        // Find the custom strategy result
        $customResults = array_filter($results, fn($r) => $r['profanity'] === 'custom_bad');
        $this->assertGreaterThanOrEqual(1, $customResults);
    }

    public function test_detect_profanities_removes_duplicate_matches()
    {
        $strategy1 = $this->createMockStrategy('strategy1', 150);
        $strategy1->method('detect')->willReturn([
            [
                'profanity' => 'bad',
                'match' => 'bad',
                'start' => 5,
                'length' => 3,
                'full_word' => 'bad'
            ]
        ]);
        
        $strategy2 = $this->createMockStrategy('strategy2', 140);
        $strategy2->method('detect')->willReturn([
            [
                'profanity' => 'bad_duplicate',
                'match' => 'bad',
                'start' => 5, // Same position
                'length' => 3, // Same length
                'full_word' => 'bad'
            ]
        ]);
        
        $this->pluginManager->registerStrategy($strategy1);
        $this->pluginManager->registerStrategy($strategy2);
        
        $results = $this->pluginManager->detectProfanities(
            'This bad word',
            ['bad' => '/bad/i'],
            []
        );
        
        // Should only have one match (duplicate removed)
        $this->assertCount(1, $results);
        $this->assertEquals('bad', $results[0]['profanity']);
    }

    public function test_detect_profanities_sorts_matches_by_position()
    {
        $strategy = $this->createMockStrategy('custom', 150);
        $strategy->method('detect')->willReturn([
            [
                'profanity' => 'second',
                'match' => 'second',
                'start' => 20,
                'length' => 6,
                'full_word' => 'second'
            ],
            [
                'profanity' => 'first',
                'match' => 'first',
                'start' => 5,
                'length' => 5,
                'full_word' => 'first'
            ]
        ]);
        
        $this->pluginManager->registerStrategy($strategy);
        
        $results = $this->pluginManager->detectProfanities(
            'This first word and second word',
            ['first' => '/first/i', 'second' => '/second/i'],
            []
        );
        
        // Should be sorted by position
        $this->assertCount(2, $results);
        $this->assertEquals(5, $results[0]['start']);  // first
        $this->assertEquals(20, $results[1]['start']); // second
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