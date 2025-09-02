<?php

namespace Blaspsoft\Blasp\Strategies;

use Blaspsoft\Blasp\Contracts\DetectionStrategyInterface;

class GamingDetectionStrategy implements DetectionStrategyInterface
{
    private array $gamingProfanities = [
        'noob',
        'scrub',
        'trash',
        'ez',
        'rekt',
        'pwned',
        'git gud',
        'camping',
        'cheater',
        'hacker'
    ];

    public function getName(): string
    {
        return 'gaming';
    }

    public function getPriority(): int
    {
        return 120; // Higher than default to catch gaming-specific terms first
    }

    public function detect(string $text, array $profanityExpressions, array $falsePositives): array
    {
        $matches = [];
        $normalizedText = strtolower(preg_replace('/\s+/', ' ', $text));
        
        foreach ($this->gamingProfanities as $profanity) {
            $pattern = '/\b' . preg_quote($profanity, '/') . '\b/i';
            preg_match_all($pattern, $normalizedText, $regexMatches, PREG_OFFSET_CAPTURE);

            if (!empty($regexMatches[0])) {
                foreach ($regexMatches[0] as $match) {
                    $start = $match[1];
                    $length = strlen($match[0]);

                    // Check if it's not a false positive
                    if (!$this->isFalsePositive($match[0], $falsePositives)) {
                        $matches[] = [
                            'profanity' => $profanity,
                            'match' => $match[0],
                            'start' => $start,
                            'length' => $length,
                            'full_word' => $match[0],
                            'strategy' => 'gaming'
                        ];
                    }
                }
            }
        }

        return $matches;
    }

    public function canHandle(string $text, array $context = []): bool
    {
        // Check if context indicates this is gaming-related
        if (isset($context['domain']) && $context['domain'] === 'gaming') {
            return true;
        }

        // Check if context has gaming keywords
        if (isset($context['tags']) && array_intersect($context['tags'], ['gaming', 'esports', 'multiplayer'])) {
            return true;
        }

        // Simple heuristic: check for gaming-related words in text
        $gamingKeywords = ['game', 'match', 'player', 'team', 'kill', 'score', 'level'];
        $textLower = strtolower($text);
        
        foreach ($gamingKeywords as $keyword) {
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