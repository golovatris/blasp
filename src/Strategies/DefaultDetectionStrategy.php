<?php

namespace Blaspsoft\Blasp\Strategies;

use Blaspsoft\Blasp\Abstracts\BaseDetectionStrategy;

class DefaultDetectionStrategy extends BaseDetectionStrategy
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
                        $matches[] = $this->createMatchResult(
                            $profanity,
                            $match[0], 
                            $start, 
                            $length, 
                            $fullWord,
                            $this->getName()
                        );
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

}