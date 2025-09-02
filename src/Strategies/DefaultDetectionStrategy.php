<?php

namespace Blaspsoft\Blasp\Strategies;

use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;

class DefaultDetectionStrategy implements DetectionStrategyInterface
{
    public function getName(): string
    {
        return 'default';
    }

    public function getPriority(): int
    {
        return 100; // Standard priority
    }

    public function detect(string $text, array $profanityExpressions, array $falsePositives): array
    {
        $matches = [];
        $normalizedText = preg_replace('/\s+/', ' ', $text);
        
        foreach ($profanityExpressions as $profanity => $expression) {
            preg_match_all($expression, $normalizedText, $regexMatches, PREG_OFFSET_CAPTURE);

            if (!empty($regexMatches[0])) {
                foreach ($regexMatches[0] as $match) {
                    $start = $match[1];
                    $length = strlen($match[0]);

                    // Use boundaries to extract the full word around the match
                    $fullWord = $this->getFullWordContext($normalizedText, $start, $length);

                    // Check if the full word is in the false positives list
                    if (!$this->isFalsePositive($fullWord, $falsePositives)) {
                        $matches[] = [
                            'profanity' => $profanity,
                            'match' => $match[0],
                            'start' => $start,
                            'length' => $length,
                            'full_word' => $fullWord
                        ];
                    }
                }
            }
        }

        return $matches;
    }

    public function canHandle(string $text, array $context = []): bool
    {
        // Default strategy can handle any text
        return true;
    }

    /**
     * Get the full word context surrounding the matched profanity.
     *
     * @param string $string
     * @param int $start
     * @param int $length
     * @return string
     */
    private function getFullWordContext(string $string, int $start, int $length): string
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
     * Check if a word is a false positive.
     *
     * @param string $word
     * @param array $falsePositives
     * @return bool
     */
    private function isFalsePositive(string $word, array $falsePositives): bool
    {
        return in_array(strtolower($word), $falsePositives, true);
    }
}