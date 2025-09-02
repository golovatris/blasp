<?php

namespace Blaspsoft\Blasp\Config;

use Blaspsoft\Blasp\Contracts\DetectionConfigInterface;
use Blaspsoft\Blasp\Contracts\MultiLanguageConfigInterface;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;

/**
 * Configuration loader with caching support for profanity detection.
 * 
 * Handles loading and caching of detection configurations, including 
 * single-language and multi-language configurations with automatic 
 * cache invalidation and optimization.
 * 
 * @package Blaspsoft\Blasp\Config
 * @author Blasp Package
 * @since 3.0.0
 */
class ConfigurationLoader
{
    private const CACHE_TTL = 86400; // 24 hours

    public function __construct(
        private ?ExpressionGeneratorInterface $expressionGenerator = null
    ) {
    }

    /**
     * Load configuration with optional custom profanities and false positives.
     *
     * @param array|null $customProfanities
     * @param array|null $customFalsePositives
     * @return DetectionConfigInterface
     */
    public function load(?array $customProfanities = null, ?array $customFalsePositives = null): DetectionConfigInterface
    {
        $profanities = $customProfanities ?? config('blasp.profanities');
        $falsePositives = $customFalsePositives ?? config('blasp.false_positives');
        $separators = config('blasp.separators');
        $substitutions = config('blasp.substitutions');

        $config = new DetectionConfig(
            $profanities,
            $falsePositives,
            $separators,
            $substitutions,
            $this->expressionGenerator
        );

        return $this->loadFromCacheOrGenerate($config);
    }

    /**
     * Load multi-language configuration.
     *
     * @param array $languageData
     * @param string $defaultLanguage
     * @return MultiLanguageConfigInterface
     */
    public function loadMultiLanguage(array $languageData = [], string $defaultLanguage = 'english'): MultiLanguageConfigInterface
    {
        // If no language data provided, load from config
        if (empty($languageData)) {
            $languageData = [
                'english' => [
                    'profanities' => config('blasp.profanities'),
                    'false_positives' => config('blasp.false_positives')
                ]
            ];
        }

        $separators = config('blasp.separators');
        $substitutions = config('blasp.substitutions');

        $config = new MultiLanguageDetectionConfig(
            $languageData,
            $separators,
            $substitutions,
            $defaultLanguage,
            $this->expressionGenerator
        );

        return $this->loadFromCacheOrGenerate($config);
    }

    /**
     * Try to load configuration from cache, otherwise generate and cache it.
     *
     * @param DetectionConfigInterface $config
     * @return DetectionConfigInterface
     */
    private function loadFromCacheOrGenerate(DetectionConfigInterface $config): DetectionConfigInterface
    {
        $cacheKey = $config->getCacheKey();
        $cached = cache()->get($cacheKey);
        
        if ($cached) {
            return $this->loadFromCache($cached);
        }

        $this->cacheConfiguration($config, $cacheKey);
        return $config;
    }

    /**
     * Load configuration from cache data.
     *
     * @param array $cached
     * @return DetectionConfigInterface
     */
    private function loadFromCache(array $cached): DetectionConfigInterface
    {
        // Check if this is a multi-language configuration
        if (isset($cached['language_data'])) {
            return new MultiLanguageDetectionConfig(
                $cached['language_data'],
                $cached['separators'],
                $cached['substitutions'],
                $cached['default_language'] ?? 'english',
                $this->expressionGenerator
            );
        }

        return new DetectionConfig(
            $cached['profanities'],
            $cached['falsePositives'],
            $cached['separators'],
            $cached['substitutions'],
            $this->expressionGenerator
        );
    }

    /**
     * Cache the configuration.
     *
     * @param DetectionConfigInterface $config
     * @param string $cacheKey
     * @return void
     */
    private function cacheConfiguration(DetectionConfigInterface $config, string $cacheKey): void
    {
        $configToCache = [
            'profanities' => $config->getProfanities(),
            'falsePositives' => $config->getFalsePositives(),
            'separators' => $config->getSeparators(),
            'substitutions' => $config->getSubstitutions(),
        ];

        // Add multi-language specific data if applicable
        if ($config instanceof MultiLanguageConfigInterface) {
            $languageData = [];
            foreach ($config->getAvailableLanguages() as $language) {
                $languageData[$language] = [
                    'profanities' => $config->getProfanitiesForLanguage($language),
                    'false_positives' => $config->getFalsePositivesForLanguage($language)
                ];
            }
            
            $configToCache['language_data'] = $languageData;
            $configToCache['default_language'] = $config->getCurrentLanguage();
        }

        cache()->put($cacheKey, $configToCache, self::CACHE_TTL);
        $this->trackCacheKey($cacheKey);
    }

    /**
     * Track cache key for later cleanup.
     *
     * @param string $cacheKey
     * @return void
     */
    private function trackCacheKey(string $cacheKey): void
    {
        $cache = cache();
        $keys = $cache->get('blasp_cache_keys', []);
        
        if (!in_array($cacheKey, $keys)) {
            $keys[] = $cacheKey;
            $cache->put('blasp_cache_keys', $keys, self::CACHE_TTL);
        }
    }

    /**
     * Clear all cached configurations.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        $cache = cache();
        
        $keys = $cache->get('blasp_cache_keys', []);
        foreach ($keys as $key) {
            $cache->forget($key);
        }
        
        $cache->forget('blasp_cache_keys');
    }
}