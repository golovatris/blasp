<?php

namespace Blaspsoft\Blasp\Abstracts;

use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;

/**
 * Base detection strategy with common functionality for profanity detection.
 * 
 * Provides shared methods for false positive checking and word context extraction
 * to avoid code duplication across different detection strategies.
 * 
 * @package Blaspsoft\Blasp\Abstracts
 * @author Blasp Package
 * @since 3.0.0
 */
abstract class BaseDetectionStrategy implements DetectionStrategyInterface
{
    /**
     * Check if a word is a false positive.
     *
     * @param string $word
     * @param array $falsePositives
     * @return bool
     */
    protected function isFalsePositive(string $word, array $falsePositives): bool
    {
        return in_array(strtolower($word), $falsePositives, true);
    }

    /**
     * Get the full word context surrounding the matched profanity.
     *
     * @param string $string
     * @param int $start
     * @param int $length
     * @return string
     */
    protected function getFullWordContext(string $string, int $start, int $length): string
    {
        // Define word boundaries (spaces, punctuation, etc.)
        $left = $start;
        $right = $start + $length;

        // Move the left pointer backwards to find the start of the full word
        while ($left > 0 && preg_match('/\w/', $string[$left - 1])) {
            $left--;
        }

        // Move the right pointer forwards to find the end of the full word
        while ($right < strlen($string) && preg_match('/\w/', $string[$right])) {
            $right++;
        }

        // Return the full word surrounding the matched profanity
        return substr($string, $left, $right - $left);
    }

    /**
     * Create a standard match result array.
     *
     * @param string $profanity
     * @param string $match
     * @param int $start
     * @param int $length
     * @param string $fullWord
     * @param string $strategy
     * @return array
     */
    protected function createMatchResult(string $profanity, string $match, int $start, int $length, string $fullWord, string $strategy): array
    {
        return [
            'profanity' => $profanity,
            'match' => $match,
            'start' => $start,
            'length' => $length,
            'full_word' => $fullWord,
            'strategy' => $strategy
        ];
    }
}