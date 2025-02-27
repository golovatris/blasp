<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

class Normalize
{
    public static function getLanguageNormalizerInstance(): StringNormalizer
    {
        return new EnglishStringNormalizer();
    }
}