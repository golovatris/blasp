<?php

namespace Blaspsoft\Blasp\Plugins;

use Blaspsoft\Blasp\Registries\DetectionStrategyRegistry;
use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use Blaspsoft\Blasp\Strategies\DefaultDetectionStrategy;

class PluginManager
{
    private DetectionStrategyRegistry $strategyRegistry;

    public function __construct(?DetectionStrategyRegistry $strategyRegistry = null)
    {
        $this->strategyRegistry = $strategyRegistry ?? new DetectionStrategyRegistry();
        $this->registerDefaultStrategies();
    }

    /**
     * Register a new detection strategy.
     *
     * @param DetectionStrategyInterface $strategy
     * @return void
     */
    public function registerStrategy(DetectionStrategyInterface $strategy): void
    {
        $this->strategyRegistry->register($strategy->getName(), $strategy);
    }

    /**
     * Remove a detection strategy.
     *
     * @param string $strategyName
     * @return void
     */
    public function removeStrategy(string $strategyName): void
    {
        $this->strategyRegistry->remove($strategyName);
    }

    /**
     * Get all registered strategies.
     *
     * @return array<DetectionStrategyInterface>
     */
    public function getAllStrategies(): array
    {
        return $this->strategyRegistry->getAllByPriority();
    }

    /**
     * Get strategies that can handle the given text/context.
     *
     * @param string $text
     * @param array $context
     * @return array<DetectionStrategyInterface>
     */
    public function getApplicableStrategies(string $text, array $context = []): array
    {
        return $this->strategyRegistry->getApplicableStrategies($text, $context);
    }

    /**
     * Run profanity detection using all applicable strategies.
     *
     * @param string $text
     * @param array $profanityExpressions
     * @param array $falsePositives
     * @param array $context
     * @return array
     */
    public function detectProfanities(
        string $text,
        array $profanityExpressions,
        array $falsePositives,
        array $context = []
    ): array {
        $allMatches = [];
        $strategies = $this->getApplicableStrategies($text, $context);

        foreach ($strategies as $strategy) {
            $matches = $strategy->detect($text, $profanityExpressions, $falsePositives);
            
            // Merge matches, avoiding duplicates based on position
            foreach ($matches as $match) {
                $key = $match['start'] . '_' . $match['length'];
                if (!isset($allMatches[$key])) {
                    $allMatches[$key] = $match;
                }
            }
        }

        // Sort matches by position
        uasort($allMatches, function ($a, $b) {
            return $a['start'] <=> $b['start'];
        });

        return array_values($allMatches);
    }

    /**
     * Check if a strategy is registered.
     *
     * @param string $strategyName
     * @return bool
     */
    public function hasStrategy(string $strategyName): bool
    {
        return $this->strategyRegistry->has($strategyName);
    }

    /**
     * Get a specific strategy by name.
     *
     * @param string $strategyName
     * @return DetectionStrategyInterface
     */
    public function getStrategy(string $strategyName): DetectionStrategyInterface
    {
        return $this->strategyRegistry->get($strategyName);
    }

    /**
     * Register default detection strategies.
     *
     * @return void
     */
    private function registerDefaultStrategies(): void
    {
        $this->registerStrategy(new DefaultDetectionStrategy());
    }
}