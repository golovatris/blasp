<?php

namespace Blaspsoft\Blasp\Factories;

use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use Blaspsoft\Blasp\Strategies\DefaultDetectionStrategy;
use Blaspsoft\Blasp\Strategies\GamingDetectionStrategy;
use Blaspsoft\Blasp\Strategies\SocialMediaDetectionStrategy;
use Blaspsoft\Blasp\Strategies\WorkplaceDetectionStrategy;
use InvalidArgumentException;

class StrategyFactory
{
    private static array $availableStrategies = [
        'default' => DefaultDetectionStrategy::class,
        'gaming' => GamingDetectionStrategy::class,
        'social_media' => SocialMediaDetectionStrategy::class,
        'workplace' => WorkplaceDetectionStrategy::class,
    ];

    /**
     * Create a detection strategy by name.
     *
     * @param string $strategyName
     * @return DetectionStrategyInterface
     * @throws InvalidArgumentException
     */
    public static function create(string $strategyName): DetectionStrategyInterface
    {
        $strategyName = strtolower($strategyName);
        
        if (!isset(self::$availableStrategies[$strategyName])) {
            throw new InvalidArgumentException("Unknown strategy: {$strategyName}. Available strategies: " . implode(', ', array_keys(self::$availableStrategies)));
        }

        $strategyClass = self::$availableStrategies[$strategyName];
        return new $strategyClass();
    }

    /**
     * Get all available strategy names.
     *
     * @return array
     */
    public static function getAvailableStrategies(): array
    {
        return array_keys(self::$availableStrategies);
    }

    /**
     * Create multiple strategies at once.
     *
     * @param array $strategyNames
     * @return array<DetectionStrategyInterface>
     */
    public static function createMultiple(array $strategyNames): array
    {
        $strategies = [];
        
        foreach ($strategyNames as $strategyName) {
            $strategies[] = self::create($strategyName);
        }

        return $strategies;
    }

    /**
     * Register a custom strategy class.
     *
     * @param string $name
     * @param string $strategyClass
     * @return void
     * @throws InvalidArgumentException
     */
    public static function registerStrategy(string $name, string $strategyClass): void
    {
        if (!class_exists($strategyClass)) {
            throw new InvalidArgumentException("Strategy class does not exist: {$strategyClass}");
        }

        if (!is_subclass_of($strategyClass, DetectionStrategyInterface::class)) {
            throw new InvalidArgumentException("Strategy class must implement DetectionStrategyInterface: {$strategyClass}");
        }

        self::$availableStrategies[strtolower($name)] = $strategyClass;
    }

    /**
     * Create a strategy for a specific domain context.
     *
     * @param array $context
     * @return array<DetectionStrategyInterface>
     */
    public static function createForContext(array $context): array
    {
        $strategies = [self::create('default')]; // Always include default

        // Gaming context
        if (isset($context['domain']) && $context['domain'] === 'gaming') {
            $strategies[] = self::create('gaming');
        }

        // Social media context
        if (isset($context['platform']) && in_array($context['platform'], ['twitter', 'facebook', 'instagram', 'tiktok'])) {
            $strategies[] = self::create('social_media');
        }

        // Workplace context
        if (isset($context['environment']) && $context['environment'] === 'workplace') {
            $strategies[] = self::create('workplace');
        }

        // Remove duplicates based on strategy name
        $uniqueStrategies = [];
        $strategyNames = [];
        
        foreach ($strategies as $strategy) {
            if (!in_array($strategy->getName(), $strategyNames)) {
                $uniqueStrategies[] = $strategy;
                $strategyNames[] = $strategy->getName();
            }
        }

        return $uniqueStrategies;
    }
}