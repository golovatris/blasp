<?php
namespace Blaspsoft\Blasp;

class ProfanityDetector
{

    /**
     * An array containing all profanities, substitutions
     * and separator variants.
     *
     * @var array
     */
    protected array $profanityExpressions;

    /**
     * An array of false positive expressions
     *
     * @var array
     */
    protected array $falsePositives;

    /**
     * Cached sorted profanity expressions to avoid repeated sorting
     *
     * @var array|null
     */
    protected ?array $sortedProfanityExpressions = null;

    /**
     * Hash map of false positives for O(1) lookup performance
     *
     * @var array
     */
    protected array $falsePositivesMap;

    public function __construct(array $profanityExpressions, array $falsePositives)
    {
        $this->profanityExpressions = $profanityExpressions;
        $this->falsePositives = $falsePositives;
        
        // Pre-compute false positives hash map for faster lookups
        $this->falsePositivesMap = array_flip(array_map('mb_strtolower', $falsePositives));
    }

    /**
     *  Return an array containing all profanities, substitutions
     *  and separator variants.
     *
     * @return array
     */
    public function getProfanityExpressions(): array
    {
        // Use cached sorted expressions to avoid repeated sorting
        if ($this->sortedProfanityExpressions === null) {
            $this->sortedProfanityExpressions = $this->profanityExpressions;
            uksort($this->sortedProfanityExpressions, function($a, $b) {
                return strlen($b) - strlen($a);  // Sort by length, descending
            });
        }

        return $this->sortedProfanityExpressions;
    }

    /**
     * Determine if an expression is a false positive
     *
     * @param string $word
     * @return bool
     */
    public function isFalsePositive(string $word): bool
    {
        // Use hash map for O(1) lookup instead of O(n) in_array
        return isset($this->falsePositivesMap[mb_strtolower($word)]);
    }
}
