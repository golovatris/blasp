<?php

namespace Blaspsoft\Blasp\Contracts;

interface DetectionStrategyInterface
{
    /**
     * Get the name/identifier of this detection strategy.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the priority of this strategy (higher numbers run first).
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Detect profanities in the given text using this strategy.
     *
     * @param string $text The normalized text to check
     * @param array $profanityExpressions Available profanity expressions
     * @param array $falsePositives List of false positives to ignore
     * @return array Array of detected matches with positions
     */
    public function detect(string $text, array $profanityExpressions, array $falsePositives): array;

    /**
     * Check if this strategy can handle the given text/context.
     *
     * @param string $text
     * @param array $context Additional context information
     * @return bool
     */
    public function canHandle(string $text, array $context = []): bool;
}