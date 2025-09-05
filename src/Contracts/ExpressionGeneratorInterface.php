<?php

namespace Blaspsoft\Blasp\Contracts;

interface ExpressionGeneratorInterface
{
    /**
     * Generate profanity expressions from the given configuration.
     *
     * @param array $profanities
     * @param array $separators
     * @param array $substitutions
     * @return array<string, string> Array of profanity => regex expression pairs
     */
    public function generateExpressions(array $profanities, array $separators, array $substitutions): array;

    /**
     * Generate separator expression from separators array.
     *
     * @param array $separators
     * @return string
     */
    public function generateSeparatorExpression(array $separators): string;

    /**
     * Generate character substitution expressions.
     *
     * @param array $substitutions
     * @return array<string, string>
     */
    public function generateSubstitutionExpressions(array $substitutions): array;

    /**
     * Generate a single profanity regex expression.
     *
     * @param string $profanity
     * @param array $substitutions
     * @param string $separatorExpression
     * @return string
     */
    public function generateProfanityExpression(string $profanity, array $substitutions, string $separatorExpression): string;
}