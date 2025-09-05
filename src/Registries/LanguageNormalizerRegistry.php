<?php

namespace Blaspsoft\Blasp\Registries;

use Blaspsoft\Blasp\Contracts\RegistryInterface;
use Blaspsoft\Blasp\Abstracts\StringNormalizer;
use InvalidArgumentException;

class LanguageNormalizerRegistry implements RegistryInterface
{
    /**
     * @var array<string, StringNormalizer>
     */
    private array $normalizers = [];

    /**
     * @var string
     */
    private string $defaultLanguage = 'english';

    /**
     * Register a normalizer for a specific language.
     *
     * @param string $key
     * @param StringNormalizer $item
     * @return void
     */
    public function register(string $key, mixed $item): void
    {
        if (!$item instanceof StringNormalizer) {
            throw new InvalidArgumentException('Item must be an instance of StringNormalizer');
        }

        $this->normalizers[strtolower($key)] = $item;
    }

    /**
     * Get a normalizer for a specific language.
     *
     * @param string $key
     * @return StringNormalizer
     * @throws InvalidArgumentException
     */
    public function get(string $key): mixed
    {
        $language = strtolower($key);
        
        if (!$this->has($language)) {
            throw new InvalidArgumentException("No normalizer registered for language: {$key}");
        }

        return $this->normalizers[$language];
    }

    /**
     * Check if a normalizer exists for a language.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->normalizers[strtolower($key)]);
    }

    /**
     * Get all registered normalizers.
     *
     * @return array<string, StringNormalizer>
     */
    public function all(): array
    {
        return $this->normalizers;
    }

    /**
     * Get the default normalizer instance.
     *
     * @return StringNormalizer
     */
    public function getDefault(): StringNormalizer
    {
        return $this->get($this->defaultLanguage);
    }

    /**
     * Set the default language.
     *
     * @param string $language
     * @return void
     */
    public function setDefaultLanguage(string $language): void
    {
        $this->defaultLanguage = strtolower($language);
    }
}