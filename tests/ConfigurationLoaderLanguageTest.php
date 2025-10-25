<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Config\ConfigurationLoader;
use Blaspsoft\Blasp\Contracts\MultiLanguageConfigInterface;

class ConfigurationLoaderLanguageTest extends TestCase
{
    private ConfigurationLoader $loader;

    public function setUp(): void
    {
        parent::setUp();
        $this->loader = new ConfigurationLoader();
    }

    public function test_load_multi_language_with_language_files()
    {
        $config = $this->loader->loadMultiLanguage();
        
        $this->assertInstanceOf(MultiLanguageConfigInterface::class, $config);
        $this->assertContains('english', $config->getAvailableLanguages());
        $this->assertContains('spanish', $config->getAvailableLanguages());
        $this->assertContains('french', $config->getAvailableLanguages());
        $this->assertContains('german', $config->getAvailableLanguages());
        $this->assertContains('russian', $config->getAvailableLanguages());
    }

    public function test_get_available_languages()
    {
        $languages = $this->loader->getAvailableLanguages();
        
        $this->assertIsArray($languages);
        $this->assertContains('english', $languages);
        $this->assertContains('spanish', $languages);
        $this->assertContains('french', $languages);
        $this->assertContains('german', $languages);
        $this->assertContains('russian', $languages);
    }

    public function test_load_specific_language_english()
    {
        $englishConfig = $this->loader->loadLanguage('english');
        
        $this->assertIsArray($englishConfig);
        $this->assertArrayHasKey('profanities', $englishConfig);
        $this->assertArrayHasKey('false_positives', $englishConfig);
        $this->assertIsArray($englishConfig['profanities']);
        $this->assertIsArray($englishConfig['false_positives']);
        
        // Test some known English profanities
        $this->assertContains('fuck', $englishConfig['profanities']);
        $this->assertContains('shit', $englishConfig['profanities']);
        
        // Test some known English false positives
        $this->assertContains('class', $englishConfig['false_positives']);
        $this->assertContains('pass', $englishConfig['false_positives']);
    }

    public function test_load_specific_language_spanish()
    {
        $spanishConfig = $this->loader->loadLanguage('spanish');
        
        $this->assertIsArray($spanishConfig);
        $this->assertArrayHasKey('profanities', $spanishConfig);
        $this->assertArrayHasKey('false_positives', $spanishConfig);
        $this->assertArrayHasKey('substitutions', $spanishConfig);
        $this->assertIsArray($spanishConfig['profanities']);
        $this->assertIsArray($spanishConfig['false_positives']);
        $this->assertIsArray($spanishConfig['substitutions']);
        
        // Test some known Spanish profanities
        $this->assertContains('mierda', $spanishConfig['profanities']);
        $this->assertContains('joder', $spanishConfig['profanities']);
        $this->assertContains('cabrón', $spanishConfig['profanities']);
        
        // Test some known Spanish false positives
        $this->assertContains('clase', $spanishConfig['false_positives']);
        $this->assertContains('análisis', $spanishConfig['false_positives']);
        
        // Test Spanish-specific substitutions
        $this->assertArrayHasKey('/ñ/', $spanishConfig['substitutions']);
        $this->assertArrayHasKey('/á/', $spanishConfig['substitutions']);
    }

    public function test_load_specific_language_french()
    {
        $frenchConfig = $this->loader->loadLanguage('french');
        
        $this->assertIsArray($frenchConfig);
        $this->assertArrayHasKey('profanities', $frenchConfig);
        $this->assertArrayHasKey('false_positives', $frenchConfig);
        $this->assertArrayHasKey('substitutions', $frenchConfig);
        $this->assertIsArray($frenchConfig['profanities']);
        $this->assertIsArray($frenchConfig['false_positives']);
        $this->assertIsArray($frenchConfig['substitutions']);
        
        // Test some known French profanities
        $this->assertContains('merde', $frenchConfig['profanities']);
        $this->assertContains('putain', $frenchConfig['profanities']);
        $this->assertContains('connard', $frenchConfig['profanities']);
        
        // Test some known French false positives
        $this->assertContains('classe', $frenchConfig['false_positives']);
        $this->assertContains('analyse', $frenchConfig['false_positives']);
        
        // Test French-specific substitutions
        $this->assertArrayHasKey('/à/', $frenchConfig['substitutions']);
        $this->assertArrayHasKey('/é/', $frenchConfig['substitutions']);
        $this->assertArrayHasKey('/ç/', $frenchConfig['substitutions']);
    }

    public function test_load_specific_language_russian()
    {
        $russianConfig = $this->loader->loadLanguage('russian');
        
        $this->assertIsArray($russianConfig);
        $this->assertArrayHasKey('profanities', $russianConfig);
        $this->assertArrayHasKey('false_positives', $russianConfig);
        $this->assertArrayHasKey('substitutions', $russianConfig);
        $this->assertIsArray($russianConfig['false_positives']);
        $this->assertIsArray($russianConfig['substitutions']);
        
        // Test some known Russian profanities
        $this->assertContains('ебать', $russianConfig['profanities']);
        $this->assertContains('ебаться', $russianConfig['profanities']);

        // Test some known Russian false positives
        // $this->assertContains('', $russianConfig['false_positives']);

        // Test Russian-specific substitutions
        $this->assertArrayHasKey('/е/', $russianConfig['substitutions']);
        $this->assertArrayHasKey('/и/', $russianConfig['substitutions']);
    }

    public function test_load_specific_language_german()
    {
        $germanConfig = $this->loader->loadLanguage('german');
        
        $this->assertIsArray($germanConfig);
        $this->assertArrayHasKey('profanities', $germanConfig);
        $this->assertArrayHasKey('false_positives', $germanConfig);
        $this->assertArrayHasKey('substitutions', $germanConfig);
        $this->assertIsArray($germanConfig['profanities']);
        $this->assertIsArray($germanConfig['false_positives']);
        $this->assertIsArray($germanConfig['substitutions']);
        
        // Test some known German profanities
        $this->assertContains('scheiße', $germanConfig['profanities']);
        $this->assertContains('ficken', $germanConfig['profanities']);
        $this->assertContains('arsch', $germanConfig['profanities']);
        
        // Test some known German false positives
        $this->assertContains('klasse', $germanConfig['false_positives']);
        $this->assertContains('analyse', $germanConfig['false_positives']);
        
        // Test German-specific substitutions
        $this->assertArrayHasKey('/ä/', $germanConfig['substitutions']);
        $this->assertArrayHasKey('/ö/', $germanConfig['substitutions']);
        $this->assertArrayHasKey('/ü/', $germanConfig['substitutions']);
        $this->assertArrayHasKey('/ß/', $germanConfig['substitutions']);
    }

    public function test_load_nonexistent_language()
    {
        $result = $this->loader->loadLanguage('nonexistent');
        $this->assertNull($result);
    }

    public function test_multi_language_config_language_switching()
    {
        $config = $this->loader->loadMultiLanguage();
        
        // Test default language
        $this->assertEquals('english', $config->getCurrentLanguage());
        
        // Test switching to Spanish
        $config->setLanguage('spanish');
        $this->assertEquals('spanish', $config->getCurrentLanguage());
        
        // Test getting profanities for current language (Spanish)
        $profanities = $config->getProfanities();
        $this->assertContains('mierda', $profanities);
        $this->assertContains('joder', $profanities);
        
        // Test switching to German
        $config->setLanguage('german');
        $this->assertEquals('german', $config->getCurrentLanguage());
        
        // Test getting profanities for current language (German)
        $profanities = $config->getProfanities();
        $this->assertContains('scheiße', $profanities);
        $this->assertContains('ficken', $profanities);
    }

    public function test_multi_language_config_specific_language_methods()
    {
        $config = $this->loader->loadMultiLanguage();
        
        // Test getting Spanish profanities specifically
        $spanishProfanities = $config->getProfanitiesForLanguage('spanish');
        $this->assertContains('mierda', $spanishProfanities);
        $this->assertContains('joder', $spanishProfanities);
        
        // Test getting German profanities specifically
        $germanProfanities = $config->getProfanitiesForLanguage('german');
        $this->assertContains('scheiße', $germanProfanities);
        $this->assertContains('ficken', $germanProfanities);
        
        // Test getting French false positives specifically
        $frenchFalsePositives = $config->getFalsePositivesForLanguage('french');
        $this->assertContains('classe', $frenchFalsePositives);
        $this->assertContains('analyse', $frenchFalsePositives);

        // Test getting Russian profanities specifically
        $russianProfanities = $config->getProfanitiesForLanguage('russian');
        $this->assertContains('ебать', $russianProfanities);
        $this->assertContains('ебаться', $russianProfanities);

        // Test getting Russian false positives specifically
        // $russianFalsePositives = $config->getFalsePositivesForLanguage('russian');
        // $this->assertContains('', $russianFalsePositives);
    }

    public function test_config_cache_key_generation()
    {
        $config = $this->loader->loadMultiLanguage();
        
        $cacheKey = $config->getCacheKey();
        $this->assertIsString($cacheKey);
        $this->assertStringStartsWith('blasp_multilang_config_', $cacheKey);
        
        // Test that cache key changes when language changes
        $config->setLanguage('spanish');
        $newCacheKey = $config->getCacheKey();
        $this->assertNotEquals($cacheKey, $newCacheKey);
    }

    public function test_string_normalizer_for_languages()
    {
        $config = $this->loader->loadMultiLanguage();
        
        // Test English normalizer
        $config->setLanguage('english');
        $normalizer = $config->getStringNormalizer();
        $this->assertInstanceOf(\Blaspsoft\Blasp\Normalizers\EnglishStringNormalizer::class, $normalizer);
        
        // Test Spanish normalizer
        $config->setLanguage('spanish');
        $normalizer = $config->getStringNormalizer();
        $this->assertInstanceOf(\Blaspsoft\Blasp\Normalizers\SpanishStringNormalizer::class, $normalizer);
        
        // Test German normalizer
        $config->setLanguage('german');
        $normalizer = $config->getStringNormalizer();
        $this->assertInstanceOf(\Blaspsoft\Blasp\Normalizers\GermanStringNormalizer::class, $normalizer);
        
        // Test French normalizer
        $config->setLanguage('french');
        $normalizer = $config->getStringNormalizer();
        $this->assertInstanceOf(\Blaspsoft\Blasp\Normalizers\FrenchStringNormalizer::class, $normalizer);
    }
}