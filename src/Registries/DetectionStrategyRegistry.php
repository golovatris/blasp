<?php

namespace Blaspsoft\Blasp\Registries;

use Blaspsoft\Blasp\Contracts\RegistryInterface;
use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;
use InvalidArgumentException;

class DetectionStrategyRegistry implements RegistryInterface
{
    /**
     * @var array<string, DetectionStrategyInterface>
     */
    private array $strategies = [];

    /**
     * Register a detection strategy.
     *
     * @param string $key
     * @param DetectionStrategyInterface $item
     * @return void
     */
    public function register(string $key, mixed $item): void
    {
        if (!$item instanceof DetectionStrategyInterface) {
            throw new InvalidArgumentException('Item must be an instance of DetectionStrategyInterface');
        }

        $this->strategies[strtolower($key)] = $item;
    }

    /**
     * Get a detection strategy by key.
     *
     * @param string $key
     * @return DetectionStrategyInterface
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $strategyKey = strtolower($key);
        
        if (!$this->has($strategyKey)) {
            throw new InvalidArgumentException("No detection strategy registered with key: {$key}");
        }

        return $this->strategies[$strategyKey];
    }

    /**
     * Check if a strategy exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->strategies[strtolower($key)]);
    }

    /**
     * Get all registered strategies.
     *
     * @return array<string, DetectionStrategyInterface>
     */
    public function all(): array
    {
        return $this->strategies;
    }

    /**
     * Get all strategies sorted by priority (highest first).
     *
     * @return array<DetectionStrategyInterface>
     */
    public function getAllByPriority(): array
    {
        $strategies = array_values($this->strategies);
        
        usort($strategies, function (DetectionStrategyInterface $a, DetectionStrategyInterface $b) {
            return $b->getPriority() <=> $a->getPriority();
        });

        return $strategies;
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
        $applicable = [];
        
        foreach ($this->getAllByPriority() as $strategy) {
            if ($strategy->canHandle($text, $context)) {
                $applicable[] = $strategy;
            }
        }

        return $applicable;
    }

    /**
     * Remove a strategy from the registry.
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void
    {
        unset($this->strategies[strtolower($key)]);
    }
}