<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

class RussianStringNormalizer extends StringNormalizer
{
    public function normalize(string $string): string
    {
        return $string;
    }
}
