<?php

namespace Blaspsoft\Blasp\Factories;

use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use Blaspsoft\Blasp\Strategies\DefaultDetectionStrategy;
use InvalidArgumentException;

/**
 * Factory for creating and managing profanity detection strategies.
 * 
 * Provides methods to create individual strategies, multiple strategies,
 * and context-aware strategy selection for different domains.
 * 
 * @package Blaspsoft\Blasp\Factories
 * @author Blasp Package
 * @since 3.0.0
 */
class StrategyFactory
{
    private static array $availableStrategies = [
        'default' => DefaultDetectionStrategy::class,
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
        // For now, just return the default strategy
        // Context parameter kept for backwards compatibility
        return [self::create('default')];
    }
}