<?php

namespace Blaspsoft\Blasp\Strategies;

use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;

class WorkplaceDetectionStrategy implements DetectionStrategyInterface
{
    private array $workplaceProfanities = [
        'incompetent',
        'useless',
        'lazy',
        'pathetic',
        'worthless',
        'loser',
        'failure',
        'moron',
        'idiot',
        'stupid'
    ];

    public function getName(): string
    {
        return 'workplace';
    }

    public function getPriority(): int
    {
        return 130; // Highest priority for professional environments
    }

    public function detect(string $text, array $profanityExpressions, array $falsePositives): array
    {
        $matches = [];
        $normalizedText = strtolower(preg_replace('/\s+/', ' ', $text));
        
        foreach ($this->workplaceProfanities as $profanity) {
            $pattern = '/\b' . preg_quote($profanity, '/') . '\b/i';
            preg_match_all($pattern, $normalizedText, $regexMatches, PREG_OFFSET_CAPTURE);

            if (!empty($regexMatches[0])) {
                foreach ($regexMatches[0] as $match) {
                    $start = $match[1];
                    $length = strlen($match[0]);

                    if (!$this->isFalsePositive($match[0], $falsePositives)) {
                        $matches[] = [
                            'profanity' => $profanity,
                            'match' => $match[0],
                            'start' => $start,
                            'length' => $length,
                            'full_word' => $match[0],
                            'strategy' => 'workplace'
                        ];
                    }
                }
            }
        }

        // Check for inappropriate workplace language patterns
        $inappropriatePatterns = [
            '/\byou are\s+(useless|worthless|pathetic)/i',
            '/\bshut up\b/i',
            '/\bget lost\b/i'
        ];

        foreach ($inappropriatePatterns as $pattern) {
            if (preg_match_all($pattern, $text, $patternMatches, PREG_OFFSET_CAPTURE)) {
                foreach ($patternMatches[0] as $match) {
                    $matches[] = [
                        'profanity' => 'workplace_inappropriate',
                        'match' => $match[0],
                        'start' => $match[1],
                        'length' => strlen($match[0]),
                        'full_word' => $match[0],
                        'strategy' => 'workplace'
                    ];
                }
            }
        }

        return $matches;
    }

    public function canHandle(string $text, array $context = []): bool
    {
        // Check context for workplace indicators
        if (isset($context['environment']) && $context['environment'] === 'workplace') {
            return true;
        }

        if (isset($context['channel']) && in_array($context['channel'], ['slack', 'teams', 'email', 'corporate'])) {
            return true;
        }

        // Check for workplace-related keywords
        $workplaceKeywords = ['meeting', 'project', 'deadline', 'manager', 'colleague', 'team', 'work', 'office'];
        $textLower = strtolower($text);
        
        foreach ($workplaceKeywords as $keyword) {
            if (strpos($textLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isFalsePositive(string $word, array $falsePositives): bool
    {
        return in_array(strtolower($word), $falsePositives, true);
    }
}