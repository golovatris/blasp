<?php

namespace Blaspsoft\Blasp\Strategies;

use Blaspsoft\Blasp\Abstracts\BaseDetectionStrategy;

class SocialMediaDetectionStrategy extends BaseDetectionStrategy
{
    private array $socialMediaProfanities = [
        'hate',
        'toxic',
        'cancel',
        'simp',
        'karen',
        'boomer',
        'triggered',
        'snowflake',
        'libtard',
        'cuck'
    ];

    public function getName(): string
    {
        return 'social_media';
    }

    public function getPriority(): int
    {
        return 110; // Higher than default
    }

    public function detect(string $text, array $profanityExpressions, array $falsePositives): array
    {
        $matches = [];
        $normalizedText = strtolower(preg_replace('/\s+/', ' ', $text));
        
        foreach ($this->socialMediaProfanities as $profanity) {
            // More flexible matching for social media slang
            $pattern = '/\b' . preg_quote($profanity, '/') . '(?:ing|ed|s|er)?\b/i';
            preg_match_all($pattern, $normalizedText, $regexMatches, PREG_OFFSET_CAPTURE);

            if (!empty($regexMatches[0])) {
                foreach ($regexMatches[0] as $match) {
                    $start = $match[1];
                    $length = strlen($match[0]);

                    if (!$this->isFalsePositive($match[0], $falsePositives)) {
                        $matches[] = $this->createMatchResult(
                            $profanity,
                            $match[0], 
                            $start, 
                            $length, 
                            $match[0],
                            $this->getName()
                        );
                    }
                }
            }
        }

        // Check for hashtag-based toxicity
        if (preg_match_all('/#\w*(' . implode('|', $this->socialMediaProfanities) . ')\w*/i', $text, $hashtagMatches, PREG_OFFSET_CAPTURE)) {
            foreach ($hashtagMatches[0] as $match) {
                $matches[] = $this->createMatchResult(
                    'hashtag_profanity',
                    $match[0], 
                    $match[1], 
                    strlen($match[0]), 
                    $match[0],
                    $this->getName()
                );
            }
        }

        return $matches;
    }

    public function canHandle(string $text, array $context = []): bool
    {
        // Check context for social media indicators
        if (isset($context['platform']) && in_array($context['platform'], ['twitter', 'facebook', 'instagram', 'tiktok'])) {
            return true;
        }

        // Check for social media patterns in text
        if (preg_match('/#\w+|@\w+|RT\s|https?:\/\//', $text)) {
            return true;
        }

        // Check for social media keywords
        $socialKeywords = ['tweet', 'post', 'share', 'like', 'follow', 'hashtag'];
        $textLower = strtolower($text);
        
        foreach ($socialKeywords as $keyword) {
            if (strpos($textLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

}