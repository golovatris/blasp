<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Config\MultiLanguageDetectionConfig;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;
use InvalidArgumentException;

class MultiLanguageDetectionConfigTest extends TestCase
{
    private array $sampleLanguageData;
    private array $sampleSeparators;
    private array $sampleSubstitutions;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->sampleLanguageData = [
            'english' => [
                'profanities' => ['bad', 'evil', 'wrong'],
                'false_positives' => ['class', 'pass']
            ],
            'spanish' => [
                'profanities' => ['malo', 'evil'],
                'false_positives' => ['clase']
            ]
        ];
        
        $this->sampleSeparators = ['@', '#', '-'];
        $this->sampleSubstitutions = [
            '/a/' => ['a', '@'],
            '/e/' => ['e', '3']
        ];
    }

    public function test_constructor_sets_default_language()
    {
        $config = new MultiLanguageDetectionConfig(
            $this->sampleLanguageData,
            $this->sampleSeparators,
            $this->sampleSubstitutions,
            'spanish'
        );
        
        $this->assertEquals('spanish', $config->getCurrentLanguage());
    }

    public function test_get_available_languages()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $languages = $config->getAvailableLanguages();
        $this->assertCount(2, $languages);
        $this->assertContains('english', $languages);
        $this->assertContains('spanish', $languages);
    }

    public function test_get_profanities_returns_current_language_profanities()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        // Default is English
        $englishProfanities = $config->getProfanities();
        $this->assertEquals(['bad', 'evil', 'wrong'], $englishProfanities);
        
        // Switch to Spanish
        $config->setLanguage('spanish');
        $spanishProfanities = $config->getProfanities();
        $this->assertEquals(['malo', 'evil'], $spanishProfanities);
    }

    public function test_get_false_positives_returns_current_language_false_positives()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        // Default is English
        $englishFalsePositives = $config->getFalsePositives();
        $this->assertEquals(['class', 'pass'], $englishFalsePositives);
        
        // Switch to Spanish
        $config->setLanguage('spanish');
        $spanishFalsePositives = $config->getFalsePositives();
        $this->assertEquals(['clase'], $spanishFalsePositives);
    }

    public function test_get_profanities_for_specific_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $englishProfanities = $config->getProfanitiesForLanguage('english');
        $this->assertEquals(['bad', 'evil', 'wrong'], $englishProfanities);
        
        $spanishProfanities = $config->getProfanitiesForLanguage('spanish');
        $this->assertEquals(['malo', 'evil'], $spanishProfanities);
    }

    public function test_get_false_positives_for_specific_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $englishFalsePositives = $config->getFalsePositivesForLanguage('english');
        $this->assertEquals(['class', 'pass'], $englishFalsePositives);
        
        $spanishFalsePositives = $config->getFalsePositivesForLanguage('spanish');
        $this->assertEquals(['clase'], $spanishFalsePositives);
    }

    public function test_set_language_throws_exception_for_unknown_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Language 'french' is not available");
        
        $config->setLanguage('french');
    }

    public function test_add_profanities_for_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $config->addProfanitiesForLanguage('english', ['terrible', 'awful']);
        
        $profanities = $config->getProfanitiesForLanguage('english');
        $this->assertContains('terrible', $profanities);
        $this->assertContains('awful', $profanities);
        $this->assertContains('bad', $profanities); // Original profanities should still be there
    }

    public function test_add_profanities_for_new_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $config->addProfanitiesForLanguage('french', ['mal', 'mauvais']);
        
        $this->assertContains('french', $config->getAvailableLanguages());
        $this->assertEquals(['mal', 'mauvais'], $config->getProfanitiesForLanguage('french'));
    }

    public function test_add_false_positives_for_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $config->addFalsePositivesForLanguage('english', ['bass', 'glass']);
        
        $falsePositives = $config->getFalsePositivesForLanguage('english');
        $this->assertContains('bass', $falsePositives);
        $this->assertContains('glass', $falsePositives);
        $this->assertContains('class', $falsePositives); // Original false positives should still be there
    }

    public function test_set_profanities_updates_current_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $config->setProfanities(['new', 'profanities']);
        
        $this->assertEquals(['new', 'profanities'], $config->getProfanities());
        $this->assertEquals(['new', 'profanities'], $config->getProfanitiesForLanguage('english'));
    }

    public function test_set_false_positives_updates_current_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $config->setFalsePositives(['new', 'false_positives']);
        
        $this->assertEquals(['new', 'false_positives'], $config->getFalsePositives());
        $this->assertEquals(['new', 'false_positives'], $config->getFalsePositivesForLanguage('english'));
    }

    public function test_cache_key_includes_language()
    {
        $config = new MultiLanguageDetectionConfig($this->sampleLanguageData);
        
        $englishCacheKey = $config->getCacheKey();
        
        $config->setLanguage('spanish');
        $spanishCacheKey = $config->getCacheKey();
        
        $this->assertNotEquals($englishCacheKey, $spanishCacheKey);
        $this->assertStringStartsWith('blasp_multilang_config_', $englishCacheKey);
        $this->assertStringStartsWith('blasp_multilang_config_', $spanishCacheKey);
    }

    public function test_get_separators_and_substitutions()
    {
        $config = new MultiLanguageDetectionConfig(
            $this->sampleLanguageData,
            $this->sampleSeparators,
            $this->sampleSubstitutions
        );
        
        $this->assertEquals($this->sampleSeparators, $config->getSeparators());
        $this->assertEquals($this->sampleSubstitutions, $config->getSubstitutions());
    }

    public function test_profanity_expressions_generated_for_current_language()
    {
        $config = new MultiLanguageDetectionConfig(
            $this->sampleLanguageData,
            $this->sampleSeparators,
            $this->sampleSubstitutions
        );
        
        $expressions = $config->getProfanityExpressions();
        $this->assertIsArray($expressions);
        $this->assertNotEmpty($expressions);
        
        // Should have expressions for English profanities
        $this->assertArrayHasKey('bad', $expressions);
        $this->assertArrayHasKey('evil', $expressions);
        $this->assertArrayHasKey('wrong', $expressions);
    }

    public function test_profanity_expressions_regenerated_on_language_change()
    {
        $config = new MultiLanguageDetectionConfig(
            $this->sampleLanguageData,
            $this->sampleSeparators,
            $this->sampleSubstitutions
        );
        
        $englishExpressions = $config->getProfanityExpressions();
        
        $config->setLanguage('spanish');
        $spanishExpressions = $config->getProfanityExpressions();
        
        $this->assertNotEquals($englishExpressions, $spanishExpressions);
        $this->assertArrayHasKey('malo', $spanishExpressions);
        $this->assertArrayNotHasKey('bad', $spanishExpressions);
    }
}