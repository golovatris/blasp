<?php

namespace Blaspsoft\Blasp\Config;

use Blaspsoft\Blasp\Contracts\MultiLanguageConfigInterface;
use Blaspsoft\Blasp\Contracts\ExpressionGeneratorInterface;
use Blaspsoft\Blasp\Generators\ProfanityExpressionGenerator;
use Blaspsoft\Blasp\Normalizers\Normalize;
use Blaspsoft\Blasp\Abstracts\StringNormalizer;
use InvalidArgumentException;

/**
 * Multi-language detection configuration for profanity filtering.
 * 
 * Manages profanities, false positives, and configurations across multiple 
 * languages with dynamic language switching and expression generation.
 * 
 * @package Blaspsoft\Blasp\Config
 * @author Blasp Package
 * @since 3.0.0
 */
class MultiLanguageDetectionConfig implements MultiLanguageConfigInterface
{
    private string $currentLanguage = 'english';
    private array $languageData = [];
    private array $separators;
    private array $substitutions;
    private array $profanityExpressions = [];
    private ExpressionGeneratorInterface $expressionGenerator;

    public function __construct(
        array $languageData = [],
        array $separators = [],
        array $substitutions = [],
        string $defaultLanguage = 'english',
        ?ExpressionGeneratorInterface $expressionGenerator = null
    ) {
        $this->languageData = $languageData;
        $this->separators = $separators;
        $this->substitutions = $substitutions;
        $this->currentLanguage = $defaultLanguage;
        $this->expressionGenerator = $expressionGenerator ?? new ProfanityExpressionGenerator();
        
        $this->generateExpressions();
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function setLanguage(string $language): void
    {
        if (!$this->hasLanguage($language)) {
            throw new InvalidArgumentException("Language '{$language}' is not available");
        }
        
        $this->currentLanguage = $language;
        $this->generateExpressions();
    }

    public function getAvailableLanguages(): array
    {
        return array_keys($this->languageData);
    }

    public function getStringNormalizer(): StringNormalizer
    {
        return Normalize::getRegistry()->has($this->currentLanguage) 
            ? Normalize::getRegistry()->get($this->currentLanguage)
            : Normalize::getRegistry()->getDefault();
    }

    public function getProfanities(): array
    {
        return $this->getProfanitiesForLanguage($this->currentLanguage);
    }

    public function getFalsePositives(): array
    {
        return $this->getFalsePositivesForLanguage($this->currentLanguage);
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

    public function getProfanitiesForLanguage(string $language): array
    {
        return $this->languageData[$language]['profanities'] ?? [];
    }

    public function getFalsePositivesForLanguage(string $language): array
    {
        return $this->languageData[$language]['false_positives'] ?? [];
    }

    public function addProfanitiesForLanguage(string $language, array $profanities): void
    {
        if (!isset($this->languageData[$language])) {
            $this->languageData[$language] = [
                'profanities' => [],
                'false_positives' => []
            ];
        }

        $this->languageData[$language]['profanities'] = array_merge(
            $this->languageData[$language]['profanities'],
            $profanities
        );

        if ($language === $this->currentLanguage) {
            $this->generateExpressions();
        }
    }

    public function addFalsePositivesForLanguage(string $language, array $falsePositives): void
    {
        if (!isset($this->languageData[$language])) {
            $this->languageData[$language] = [
                'profanities' => [],
                'false_positives' => []
            ];
        }

        $this->languageData[$language]['false_positives'] = array_merge(
            $this->languageData[$language]['false_positives'],
            $falsePositives
        );
    }

    public function setProfanities(array $profanities): void
    {
        $this->languageData[$this->currentLanguage]['profanities'] = $profanities;
        $this->generateExpressions();
    }

    public function setFalsePositives(array $falsePositives): void
    {
        $this->languageData[$this->currentLanguage]['false_positives'] = $falsePositives;
    }

    public function getCacheKey(): string
    {
        $contentHash = md5(json_encode([
            'language' => $this->currentLanguage,
            'profanities' => $this->getProfanities(),
            'falsePositives' => $this->getFalsePositives(),
        ]));

        return 'blasp_multilang_config_' . $contentHash;
    }

    private function hasLanguage(string $language): bool
    {
        return isset($this->languageData[$language]);
    }

    private function generateExpressions(): void
    {
        $profanities = $this->getProfanities();
        
        if (!empty($profanities)) {
            $this->profanityExpressions = $this->expressionGenerator->generateExpressions(
                $profanities,
                $this->separators,
                $this->substitutions
            );
        }
    }
}