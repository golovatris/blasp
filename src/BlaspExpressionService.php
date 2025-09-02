<?php

namespace Blaspsoft\Blasp;

use Exception;
use Blaspsoft\Blasp\Traits\BlaspCache;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;
use Blaspsoft\Blasp\Generators\ProfanityExpressionGenerator;

abstract class BlaspExpressionService
{
    use BlaspCache;

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
     * Expression generator instance
     *
     * @var ExpressionGeneratorInterface
     */
    protected ExpressionGeneratorInterface $expressionGenerator;

    /**
     * @throws Exception
     */
    public function __construct(?array $profanities = null, ?array $falsePositives = null)
    {        
        $this->expressionGenerator = new ProfanityExpressionGenerator();
        $this->loadConfiguration($profanities, $falsePositives);

        $this->separatorExpression = $this->expressionGenerator->generateSeparatorExpression($this->separators);
        $this->characterExpressions = $this->expressionGenerator->generateSubstitutionExpressions($this->substitutions);
        $this->generateProfanityExpressionArray();
    }

    /**
     * Load Profanities, Separators and Substitutions
     * from config file or custom arrays.
     */
    private function loadConfiguration(?array $customProfanities = null, ?array $customFalsePositives = null): void
    {
        // Set the profanities and false positives
        $this->profanities = $customProfanities ?? config('blasp.profanities');
        $this->falsePositives = $customFalsePositives ?? config('blasp.false_positives');

        $this->loadFromCacheOrGenerate();
    }

    /**
     * Load configuration without using cache
     */
    private function loadUncachedConfiguration(): void
    {
        $this->separators = config('blasp.separators');
        $this->substitutions = config('blasp.substitutions');
        
        // Generate expressions
        $this->separatorExpression = $this->expressionGenerator->generateSeparatorExpression($this->separators);
        $this->characterExpressions = $this->expressionGenerator->generateSubstitutionExpressions($this->substitutions);
        $this->generateProfanityExpressionArray();
    }


    /**
     * Generate expressions foreach of the profanities
     * and order the array longest to shortest.
     *
     */
    private function generateProfanityExpressionArray(): void
    {
        foreach ($this->profanities as $profanity) {
            $this->profanityExpressions[$profanity] = $this->expressionGenerator->generateProfanityExpression(
                $profanity,
                $this->characterExpressions,
                $this->separatorExpression
            );
        }
    }
}