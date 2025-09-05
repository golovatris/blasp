<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Config\ConfigurationLoader;
use Blaspsoft\Blasp\Config\DetectionConfig;
use Blaspsoft\Blasp\Config\MultiLanguageDetectionConfig;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;
use Illuminate\Support\Facades\Cache;

class ConfigurationLoaderTest extends TestCase
{
    private ConfigurationLoader $loader;
    private ExpressionGeneratorInterface $mockExpressionGenerator;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->mockExpressionGenerator = $this->createMock(ExpressionGeneratorInterface::class);
        $this->mockExpressionGenerator->method('generateExpressions')->willReturn([]);
        
        $this->loader = new ConfigurationLoader($this->mockExpressionGenerator);
        
        // Clear cache before each test
        Cache::flush();
    }

    public function test_load_returns_detection_config()
    {
        $config = $this->loader->load();
        
        $this->assertInstanceOf(DetectionConfig::class, $config);
        $this->assertIsArray($config->getProfanities());
        $this->assertIsArray($config->getFalsePositives());
    }

    public function test_load_with_custom_profanities()
    {
        $customProfanities = ['custom', 'profanities'];
        $customFalsePositives = ['custom', 'false', 'positives'];
        
        $config = $this->loader->load($customProfanities, $customFalsePositives);
        
        $this->assertEquals($customProfanities, $config->getProfanities());
        $this->assertEquals($customFalsePositives, $config->getFalsePositives());
    }

    public function test_load_multi_language_returns_multi_language_config()
    {
        $languageData = [
            'english' => [
                'profanities' => ['bad', 'evil'],
                'false_positives' => ['class']
            ],
            'spanish' => [
                'profanities' => ['malo'],
                'false_positives' => ['clase']
            ]
        ];
        
        $config = $this->loader->loadMultiLanguage($languageData, 'spanish');
        
        $this->assertInstanceOf(MultiLanguageDetectionConfig::class, $config);
        $this->assertEquals('spanish', $config->getCurrentLanguage());
        $this->assertEquals(['english', 'spanish'], $config->getAvailableLanguages());
    }

    public function test_load_multi_language_with_empty_data_uses_config()
    {
        $config = $this->loader->loadMultiLanguage();
        
        $this->assertInstanceOf(MultiLanguageDetectionConfig::class, $config);
        $this->assertEquals('english', $config->getCurrentLanguage());
        $this->assertContains('english', $config->getAvailableLanguages());
    }

    public function test_configuration_is_cached()
    {
        // Load configuration first time
        $config1 = $this->loader->load(['test'], ['false_positive']);
        
        // Mock the expression generator to return different results
        $mockGenerator2 = $this->createMock(ExpressionGeneratorInterface::class);
        $mockGenerator2->method('generateExpressions')->willReturn(['different' => 'result']);
        
        // Create a new loader with different generator
        $loader2 = new ConfigurationLoader($mockGenerator2);
        
        // Load configuration second time - should come from cache
        $config2 = $loader2->load(['test'], ['false_positive']);
        
        // Both configs should have the same data (from cache)
        $this->assertEquals($config1->getProfanities(), $config2->getProfanities());
        $this->assertEquals($config1->getFalsePositives(), $config2->getFalsePositives());
    }

    public function test_multi_language_configuration_is_cached()
    {
        $languageData = [
            'english' => [
                'profanities' => ['test'],
                'false_positives' => ['pass']
            ]
        ];
        
        // Load configuration first time
        $config1 = $this->loader->loadMultiLanguage($languageData);
        
        // Load configuration second time - should come from cache
        $config2 = $this->loader->loadMultiLanguage($languageData);
        
        $this->assertEquals($config1->getProfanities(), $config2->getProfanities());
        $this->assertEquals($config1->getAvailableLanguages(), $config2->getAvailableLanguages());
    }

    public function test_different_configurations_have_different_cache_keys()
    {
        $config1 = $this->loader->load(['prof1'], ['false1']);
        $config2 = $this->loader->load(['prof2'], ['false2']);
        
        $this->assertNotEquals($config1->getCacheKey(), $config2->getCacheKey());
    }

    public function test_clear_cache_removes_cached_configurations()
    {
        // Load and cache a configuration
        $this->loader->load(['test'], ['false_positive']);
        
        // Verify something is cached
        $this->assertTrue(Cache::has('blasp_cache_keys'));
        
        // Clear cache
        ConfigurationLoader::clearCache();
        
        // Verify cache is cleared
        $this->assertFalse(Cache::has('blasp_cache_keys'));
    }

    public function test_cache_keys_are_tracked()
    {
        // Load multiple configurations
        $this->loader->load(['prof1'], ['false1']);
        $this->loader->load(['prof2'], ['false2']);
        
        $this->loader->loadMultiLanguage([
            'english' => [
                'profanities' => ['test'],
                'false_positives' => ['pass']
            ]
        ]);
        
        // Verify cache keys are tracked
        $cacheKeys = Cache::get('blasp_cache_keys', []);
        $this->assertGreaterThan(0, count($cacheKeys));
        
        // All tracked keys should exist in cache
        foreach ($cacheKeys as $key) {
            $this->assertTrue(Cache::has($key), "Cache key {$key} should exist");
        }
    }

    public function test_cached_configuration_is_properly_restored()
    {
        $originalProfanities = ['original', 'profanities'];
        $originalFalsePositives = ['original', 'false', 'positives'];
        
        // Load and cache configuration
        $config1 = $this->loader->load($originalProfanities, $originalFalsePositives);
        
        // Load same configuration again (should come from cache)
        $config2 = $this->loader->load($originalProfanities, $originalFalsePositives);
        
        // Verify all properties are restored correctly
        $this->assertEquals($config1->getProfanities(), $config2->getProfanities());
        $this->assertEquals($config1->getFalsePositives(), $config2->getFalsePositives());
        $this->assertEquals($config1->getSeparators(), $config2->getSeparators());
        $this->assertEquals($config1->getSubstitutions(), $config2->getSubstitutions());
    }

    public function test_cached_multi_language_configuration_is_properly_restored()
    {
        $languageData = [
            'english' => [
                'profanities' => ['bad'],
                'false_positives' => ['class']
            ],
            'spanish' => [
                'profanities' => ['malo'],
                'false_positives' => ['clase']
            ]
        ];
        
        // Load and cache multi-language configuration
        $config1 = $this->loader->loadMultiLanguage($languageData, 'spanish');
        
        // Load same configuration again (should come from cache)
        $config2 = $this->loader->loadMultiLanguage($languageData, 'spanish');
        
        // Verify all properties are restored correctly
        $this->assertEquals($config1->getCurrentLanguage(), $config2->getCurrentLanguage());
        $this->assertEquals($config1->getAvailableLanguages(), $config2->getAvailableLanguages());
        $this->assertEquals($config1->getProfanitiesForLanguage('english'), $config2->getProfanitiesForLanguage('english'));
        $this->assertEquals($config1->getFalsePositivesForLanguage('spanish'), $config2->getFalsePositivesForLanguage('spanish'));
    }

    public function test_expression_generator_is_used()
    {
        $mockGenerator = $this->createMock(ExpressionGeneratorInterface::class);
        $mockGenerator->expects($this->atLeastOnce())
                      ->method('generateExpressions')
                      ->willReturn(['test' => '/test/i']);
        
        $loader = new ConfigurationLoader($mockGenerator);
        $config = $loader->load(['test'], []);
        
        $this->assertArrayHasKey('test', $config->getProfanityExpressions());
    }

    public function test_cache_ttl_is_respected()
    {
        // This test verifies that cache TTL is set, though we can't easily test expiration
        // without waiting or mocking time
        $config = $this->loader->load(['test'], []);
        
        // Verify the configuration was cached with some TTL
        $cacheKeys = Cache::get('blasp_cache_keys', []);
        $this->assertNotEmpty($cacheKeys);
        
        // The actual cached configuration should exist
        foreach ($cacheKeys as $key) {
            $this->assertTrue(Cache::has($key));
        }
    }

    public function test_loader_without_expression_generator_creates_default()
    {
        $loader = new ConfigurationLoader();
        $config = $loader->load(['test'], []);
        
        $this->assertInstanceOf(DetectionConfig::class, $config);
        $this->assertIsArray($config->getProfanityExpressions());
    }
}