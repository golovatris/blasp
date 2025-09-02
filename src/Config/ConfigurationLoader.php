<?php

namespace Blaspsoft\Blasp\Config;

use Blaspsoft\Blasp\Contracts\DetectionConfigInterface;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;

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