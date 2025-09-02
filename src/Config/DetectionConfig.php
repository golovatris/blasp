<?php

namespace Blaspsoft\Blasp\Config;

use Blaspsoft\Blasp\Contracts\DetectionConfigInterface;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;
use Blaspsoft\Blasp\Generators\ProfanityExpressionGenerator;

class DetectionConfig implements DetectionConfigInterface
{
    private array $profanities;
    private array $falsePositives;
    private array $separators;
    private array $substitutions;
    private array $profanityExpressions = [];
    private ExpressionGeneratorInterface $expressionGenerator;

    public function __construct(
        array $profanities,
        array $falsePositives,
        array $separators,
        array $substitutions,
        ?ExpressionGeneratorInterface $expressionGenerator = null
    ) {
        $this->profanities = $profanities;
        $this->falsePositives = $falsePositives;
        $this->separators = $separators;
        $this->substitutions = $substitutions;
        $this->expressionGenerator = $expressionGenerator ?? new ProfanityExpressionGenerator();
        
        $this->generateExpressions();
    }

    public function getProfanities(): array
    {
        return $this->profanities;
    }

    public function getFalsePositives(): array
    {
        return $this->falsePositives;
    }

    public function getSeparators(): array
    {
        return $this->separators;
    }

    public function getSubstitutions(): array
    {
        return $this->substitutions;
    }

    public function getProfanityExpressions(): array
    {
        return $this->profanityExpressions;
    }

    public function setProfanities(array $profanities): void
    {
        $this->profanities = $profanities;
        $this->generateExpressions();
    }

    public function setFalsePositives(array $falsePositives): void
    {
        $this->falsePositives = $falsePositives;
    }

    public function getCacheKey(): string
    {
        $contentHash = md5(json_encode([
            'profanities' => $this->profanities,
            'falsePositives' => $this->falsePositives,
        ]));

        return 'blasp_detection_config_' . $contentHash;
    }

    private function generateExpressions(): void
    {
        $this->profanityExpressions = $this->expressionGenerator->generateExpressions(
            $this->profanities,
            $this->separators,
            $this->substitutions
        );
    }
}