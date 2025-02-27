<?php

namespace Blaspsoft\Blasp;

use Exception;

abstract class BlaspExpressionService
{
    /**
     * Value used as a the separator placeholder.
     *
     * @var string
     */
    const SEPARATOR_PLACEHOLDER = '{!!}';

    /**
     * A list of possible character separators.
     *
     * @var array
     */
    private array $separators;

    /**
     * A list of possible character substitutions.
     *
     * @var array
     */
    private array $substitutions;

    /**
     * A list of profanities to check against.
     *
     * @var array
     */
    public array $profanities;

    /**
     * Escaped separator characters
     */
    private array $escapedSeparatorCharacters = [
        '\s',
    ];

    /**
     * An array containing all profanities, substitutions
     * and separator variants.
     *
     * @var array
     */
    protected array $profanityExpressions = [];

    /**
     * An array of separator expression profanities
     *
     * @var array
     */
    protected array|string $separatorExpression;

    /**
     * An array of character expression profanities
     *
     * @var array
     */
    protected array $characterExpressions;

    /**
     * An array of false positive expressions
     *
     * @var array
     */
    protected array $falsePositives = [];

    /**
     * @throws Exception
     */
    public function __construct(?array $profanities = null, ?array $falsePositives = null)
    {        
        $this->loadConfiguration($profanities, $falsePositives);

        $this->separatorExpression = $this->generateSeparatorExpression();

        $this->characterExpressions = $this->generateSubstitutionExpression();

        $this->generateProfanityExpressionArray();
    }

    /**
     * Load Profanities, Separators and Substitutions
     * from config file.
     *
     * @throws Exception
     */
    private function loadConfiguration(array $profanities = null, array $falsePositives = null): void
    {        
        $this->profanities = $profanities ?? config('blasp.profanities');  
        $this->separators = config('blasp.separators');
        $this->substitutions = config('blasp.substitutions');

        $this->generateFalsePositiveExpressionArray($falsePositives);
    }

    /**
     * @return string
     */
    private function generateSeparatorExpression(): string
    {        
        // Get all separators except period
        $normalSeparators = array_filter($this->separators, function($sep) {
            return $sep !== '.';
        });

        // Create the pattern for normal separators
        $pattern = $this->generateEscapedExpression($normalSeparators, $this->escapedSeparatorCharacters);
        
        // Add period and 's' as optional characters that must be followed by a word character
        return '(?:' . $pattern . '|\.(?=\w)|(?:\s))*?';
    }

    /**
     * @return array
     */
    private function generateSubstitutionExpression(): array
    {
        $characterExpressions = [];

        foreach ($this->substitutions as $character => $substitutions) {

            $characterExpressions[$character] = $this->generateEscapedExpression($substitutions, [], '+') . self::SEPARATOR_PLACEHOLDER;
        }

        return $characterExpressions;
    }

    /**
     * @param array $characters
     * @param array $escapedCharacters
     * @param string $quantifier
     * @return string
     */
    private function generateEscapedExpression(array $characters = [], array $escapedCharacters = [], string $quantifier = '*?'): string
    {
        $regex = $escapedCharacters;

        foreach ($characters as $character) {

            $regex[] = preg_quote($character, '/');
        }

        return '[' . implode('', $regex) . ']' . $quantifier;
    }

    /**
     * Generate expressions foreach of the profanities
     * and order the array longest to shortest.
     *
     */
    private function generateProfanityExpressionArray(): void
    {
        $profanityCount = count($this->profanities);

        for ($i = 0; $i < $profanityCount; $i++) {

            $this->profanityExpressions[$this->profanities[$i]] = $this->generateProfanityExpression($this->profanities[$i]);
        }
    }

    /**
     * Generate a regex expression foreach profanity.
     *
     * @param $profanity
     * @return string
     */
    private function generateProfanityExpression($profanity): string
    {
        $expression = preg_replace(array_keys($this->characterExpressions), array_values($this->characterExpressions), $profanity);

        $expression = str_replace(self::SEPARATOR_PLACEHOLDER, $this->separatorExpression, $expression);

        // Allow for non-word characters or spaces around the profanity
        $expression = '/' . $expression . '/i';
        
        return $expression;
    }

    /**
     * Generate an array of false positive expressions.
     *
     * @return void
     */
    private function generateFalsePositiveExpressionArray(array $falsePositives = null): void
    {
        $this->falsePositives = array_map('strtolower', $falsePositives ?? config('blasp.false_positives'));
    }
}