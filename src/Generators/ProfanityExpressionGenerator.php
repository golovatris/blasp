<?php

namespace Blaspsoft\Blasp\Generators;

use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;

class ProfanityExpressionGenerator implements ExpressionGeneratorInterface
{
    /**
     * Value used as a the separator placeholder.
     */
    private const SEPARATOR_PLACEHOLDER = '{!!}';

    /**
     * Escaped separator characters
     */
    private const ESCAPED_SEPARATOR_CHARACTERS = ['\s'];

    /**
     * Generate profanity expressions from the given configuration.
     *
     * @param array $profanities
     * @param array $separators
     * @param array $substitutions
     * @return array<string, string>
     */
    public function generateExpressions(array $profanities, array $separators, array $substitutions): array
    {
        $separatorExpression = $this->generateSeparatorExpression($separators);
        $substitutionExpressions = $this->generateSubstitutionExpressions($substitutions);
        
        $profanityExpressions = [];
        
        foreach ($profanities as $profanity) {
            $profanityExpressions[$profanity] = $this->generateProfanityExpression(
                $profanity,
                $substitutionExpressions,
                $separatorExpression
            );
        }

        return $profanityExpressions;
    }

    /**
     * Generate separator expression from separators array.
     *
     * @param array $separators
     * @return string
     */
    public function generateSeparatorExpression(array $separators): string
    {
        // Get all separators except period
        $normalSeparators = array_filter($separators, function($sep) {
            return $sep !== '.';
        });

        // Create the pattern for normal separators
        $pattern = $this->generateEscapedExpression($normalSeparators, self::ESCAPED_SEPARATOR_CHARACTERS);
        
        // Add period and 's' as optional characters that must be followed by a word character
        return '(?:' . $pattern . '|\.(?=\w)|(?:\s))*?';
    }

    /**
     * Generate character substitution expressions.
     *
     * @param array $substitutions
     * @return array<string, string>
     */
    public function generateSubstitutionExpressions(array $substitutions): array
    {
        $characterExpressions = [];

        foreach ($substitutions as $character => $substitutionOptions) {
            $characterExpressions[$character] = $this->generateEscapedExpression($substitutionOptions, [], '+') . self::SEPARATOR_PLACEHOLDER;
        }

        return $characterExpressions;
    }

    /**
     * Generate a single profanity regex expression.
     *
     * @param string $profanity
     * @param array $substitutionExpressions
     * @param string $separatorExpression
     * @return string
     */
    public function generateProfanityExpression(string $profanity, array $substitutionExpressions, string $separatorExpression): string
    {
        $expression = preg_replace(array_keys($substitutionExpressions), array_values($substitutionExpressions), $profanity);

        $expression = str_replace(self::SEPARATOR_PLACEHOLDER, $separatorExpression, $expression);

        // Allow for non-word characters or spaces around the profanity
        $expression = '/' . $expression . '/i';
        
        return $expression;
    }

    /**
     * Generate an escaped regex expression from characters.
     *
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
}