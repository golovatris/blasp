<?php

namespace Blaspsoft\Blasp\Contracts;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

interface MultiLanguageConfigInterface extends DetectionConfigInterface
{
    /**
     * Get the current language.
     *
     * @return string
     */
    public function getCurrentLanguage(): string;

    /**
     * Set the current language.
     *
     * @param string $language
     * @return void
     */
    public function setLanguage(string $language): void;

    /**
     * Get available languages.
     *
     * @return array
     */
    public function getAvailableLanguages(): array;

    /**
     * Get the string normalizer for the current language.
     *
     * @return StringNormalizer
     */
    public function getStringNormalizer(): StringNormalizer;

    /**
     * Get profanities for a specific language.
     *
     * @param string $language
     * @return array
     */
    public function getProfanitiesForLanguage(string $language): array;

    /**
     * Get false positives for a specific language.
     *
     * @param string $language
     * @return array
     */
    public function getFalsePositivesForLanguage(string $language): array;

    /**
     * Add profanities for a specific language.
     *
     * @param string $language
     * @param array $profanities
     * @return void
     */
    public function addProfanitiesForLanguage(string $language, array $profanities): void;

    /**
     * Add false positives for a specific language.
     *
     * @param string $language
     * @param array $falsePositives
     * @return void
     */
    public function addFalsePositivesForLanguage(string $language, array $falsePositives): void;
}