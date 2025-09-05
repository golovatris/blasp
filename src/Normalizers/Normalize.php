<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;
use Blaspsoft\Blasp\Registries\LanguageNormalizerRegistry;

class Normalize
{
    private static ?LanguageNormalizerRegistry $registry = null;

    public static function getLanguageNormalizerInstance(): StringNormalizer
    {
        return self::getRegistry()->getDefault();
    }

    public static function getRegistry(): LanguageNormalizerRegistry
    {
        if (self::$registry === null) {
            self::$registry = new LanguageNormalizerRegistry();
            self::registerDefaultNormalizers();
        }

        return self::$registry;
    }

    public static function setRegistry(LanguageNormalizerRegistry $registry): void
    {
        self::$registry = $registry;
    }

    private static function registerDefaultNormalizers(): void
    {
        self::$registry->register('english', new \Blaspsoft\Blasp\Normalizers\EnglishStringNormalizer());
        self::$registry->register('french', new \Blaspsoft\Blasp\Normalizers\FrenchStringNormalizer());
        self::$registry->register('spanish', new \Blaspsoft\Blasp\Normalizers\SpanishStringNormalizer());
        self::$registry->register('german', new \Blaspsoft\Blasp\Normalizers\GermanStringNormalizer());
    }
}