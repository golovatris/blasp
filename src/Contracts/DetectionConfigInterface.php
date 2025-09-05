<?php

namespace Blaspsoft\Blasp\Contracts;

interface DetectionConfigInterface
{
    /**
     * Get the list of profanities to check against.
     *
     * @return array
     */
    public function getProfanities(): array;

    /**
     * Get the list of false positives to ignore.
     *
     * @return array
     */
    public function getFalsePositives(): array;

    /**
     * Get the list of character separators.
     *
     * @return array
     */
    public function getSeparators(): array;

    /**
     * Get the character substitution mappings.
     *
     * @return array
     */
    public function getSubstitutions(): array;

    /**
     * Get the generated profanity expressions.
     *
     * @return array
     */
    public function getProfanityExpressions(): array;

    /**
     * Set custom profanities.
     *
     * @param array $profanities
     * @return void
     */
    public function setProfanities(array $profanities): void;

    /**
     * Set custom false positives.
     *
     * @param array $falsePositives
     * @return void
     */
    public function setFalsePositives(array $falsePositives): void;

    /**
     * Generate a cache key for this configuration.
     *
     * @return string
     */
    public function getCacheKey(): string;
}